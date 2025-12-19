<?php

namespace App\Services\Valuations;

use App\Models\Valuation;
use App\Exceptions\ValuationException;
use App\Services\ValuationInterface;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ValuationOpenAI implements ValuationInterface
{
    protected Valuation $valuation;
    protected ?string $apiKey;
    protected string $baseUrl = 'https://api.openai.com/v1/chat/completions';
    protected array $debugInformation = [];

    public function __construct(Valuation $valuation)
    {
        $this->valuation = $valuation;
        $this->apiKey = config('services.openai.api_key');

        if (empty($this->apiKey)) {
            throw new ValuationException(
                $this->valuation,
                'OpenAI API key is not configured',
                null,
                'Missing OPENAI_API_KEY in environment'
            );
        }
    }

    public function getQuote(): float
    {
        $endpoint = 'https://api.openai.com/v1/responses';
        $assetQuery = $this->valuation->isin === null ? $this->valuation->name : $this->valuation->name . '(ISIN: ' . $this->valuation->isin . ')';

        // Developer (system) instructions: force format + allow web search
        $developerPrompt = <<<TXT
        You may use Web Search to find the latest market price (not purchase/subscription price) for the requested asset.
        Return ONLY a JSON object matching the provided schema. No explanations, no units, no extra fields.
        - "price": number as string (dot as decimal separator, e.g. "175.32")
        - "currency": ISO 4217 code (e.g. "EUR")
        - "date": ISO date "YYYY-MM-DD" corresponding to that price
        If not found, return: {"price":"N/A","currency":"N/A","date":"N/A"}
        TXT;

        $payload = [
            'model' => 'gpt-4o-mini',
            'input' => [
                [
                    'role' => 'developer',
                    'content' => [
                        ['type' => 'input_text', 'text' => $developerPrompt],
                    ],
                ],
                [
                    'role' => 'user',
                    'content' => [
                        ['type' => 'input_text', 'text' => $assetQuery],
                    ],
                ],
            ],
            // 'tools' => [
            //     ['type' => 'web_search'],
            // ],
            'tools' => [[
                'type' => 'web_search_preview',
                'search_context_size' => 'medium',
                'user_location' => ['type' => 'approximate', 'country' => 'FR'],
            ]],
            'tool_choice' => 'auto',
            'temperature' => 0,
            'max_output_tokens' => 100,
            // 'reasoning' => ['effort' => 'low'],
            'store' => false,
            // 'text' => ['verbosity' => 'low'],
            'text' => [
                'format' => [
                    'name' => 'quote',
                    'type' => 'json_schema',
                    'strict' => true,
                    'schema' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'properties' => [
                            'price' => ['type' => 'string', 'pattern' => '^([0-9]+(?:\\.[0-9]{1,4})|N/A)$'],
                            'currency' => ['type' => 'string', 'pattern' => '^([A-Z]{3}|N/A)$'],
                            'date' => ['type' => 'string', 'pattern' => '^(\\d{4}-\\d{2}-\\d{2}|N/A)$'],
                        ],
                        'required' => ['price', 'currency', 'date'],
                    ],
                ],
            ]
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type'  => 'application/json',
        ])->post($endpoint, $payload);

        if (! $response->successful()) {
            throw new ValuationException(
                $this->valuation,
                'OpenAI API request failed: ' . $response->body(),
                $response->status(),
                'HTTP request failed with status ' . $response->status()
            );
        }

        $data = $response->json();



        $responseText = null;

        // 1) Convenience field if the API gives it
        if (!empty($data['output_text'])) {
            $responseText = $data['output_text'];
        }

        // 2) Otherwise, find any output_text chunk and take its text (JSON)
        if (!$responseText && !empty($data['output']) && is_array($data['output'])) {
            foreach ($data['output'] as $item) {
                if (!empty($item['content']) && is_array($item['content'])) {
                    foreach ($item['content'] as $chunk) {
                        if (($chunk['type'] ?? null) === 'output_text' && isset($chunk['text'])) {
                            $responseText = $chunk['text'];
                            break 2;
                        }
                    }
                }
            }
        }

        if (!$responseText) {
            throw new ValuationException(
                $this->valuation,
                'Invalid response format from OpenAI',
                null,
                'Response structure: ' . json_encode($data, JSON_UNESCAPED_SLASHES)
            );
        }

        // Decode JSON and reformat to "price;currency;date"
        $this->debugInformation = json_decode($responseText, true);
        if (!is_array($this->debugInformation) || !isset($this->debugInformation['price'], $this->debugInformation['currency'], $this->debugInformation['date'])) {
            throw new ValuationException(
                $this->valuation,
                'Model did not return valid JSON per schema',
                null,
                'Raw: ' . $responseText
            );
        }

        // Check if currency matches the valuation's currency
        if ($this->debugInformation['currency'] !== $this->valuation->currency->symbol) {
            throw new ValuationException(
                $this->valuation,
                'Currency mismatch: OpenAI returned ' . $this->debugInformation['currency'] . ' but valuation expects ' . $this->valuation->currency->symbol,
                null,
                'Currency validation failed'
            );
        }

        $this->debugInformation['raw_response'] = $responseText;
        return (float) $this->debugInformation['price'];
    }

    // protected function parseResponse(string $response): float
    // {
    //     // Parse the response format: <price>;<ISO currency code>;<ISO date>
    //     $parts = explode(';', $response);

    //     if (count($parts) !== 3) {
    //         throw new ValuationException(
    //             $this->valuation,
    //             'Invalid response format from OpenAI',
    //             null,
    //             'Expected format: price;currency;date, got: ' . $response
    //         );
    //     }

    //     $price = trim($parts[0]);
    //     $currency = trim($parts[1]);
    //     $date = trim($parts[2]);

    //     // Check if response indicates no data found
    //     if ($price === 'N/A' || $currency === 'N/A' || $date === 'N/A') {
    //         throw new ValuationException(
    //             $this->valuation,
    //             'Asset price not found by OpenAI',
    //             null,
    //             'OpenAI could not find market price for: ' . $this->valuation->name
    //         );
    //     }

    //     // Validate price is numeric
    //     if (!is_numeric($price)) {
    //         throw new ValuationException(
    //             $this->valuation,
    //             'Invalid price format from OpenAI',
    //             null,
    //             'Price must be numeric, got: ' . $price
    //         );
    //     }

    //     // Store debug information
    //     $this->debugInformation = [
    //         'price' => $price,
    //         'currency' => $currency,
    //         'date' => $date,
    //         'raw_response' => $response
    //     ];

    //     return (float) $price;
    // }

    public function getDebugInformation(): array
    {
        return $this->debugInformation;
    }

    public static function getFields(): array
    {
        return [
            'prompt' => TextInput::make('prompt')
                ->label(__('Custom prompt'))
                ->helperText(__('Use {valuation_name} as placeholder for the valuation name. Leave empty to use default prompt.'))
                ->placeholder(__('Get the latest price of {valuation_name}')),
        ];
    }
}
