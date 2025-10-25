<?php

namespace App\Services\Valuations;

use App\Models\Valuation;
use App\Exceptions\ValuationException;
use Filament\Forms\Components\TextInput;
use App\Services\ValuationInterface;
use Illuminate\Support\Facades\Http;

class ValuationYahoo implements ValuationInterface
{
    protected Valuation $valuation;

    public function __construct(Valuation $valuation)
    {
        $this->valuation = $valuation;
    }

    public function getQuote(): float
    {
        try {
            $symbol = $this->valuation->update_data['symbol'] ?? '';
            if (empty($symbol)) {
                throw new ValuationException(
                    $this->valuation,
                    'Yahoo Finance symbol is required',
                    null,
                    'Missing field: symbol'
                );
            }

            // Use the correct Yahoo Finance API endpoint
            $url = "https://query1.finance.yahoo.com/v8/finance/chart/{$symbol}";

            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.5',
                'Accept-Encoding' => 'gzip, deflate',
                'Connection' => 'keep-alive',
                'Upgrade-Insecure-Requests' => '1',
            ])
                ->timeout(30)
                ->get($url);

            if (!$response->successful()) {
                throw new ValuationException(
                    $this->valuation,
                    "HTTP {$response->status()}: Failed to fetch data",
                    $response->status(),
                    "HTTP {$response->status()}: Failed to fetch data"
                );
            }

            $data = $response->json();

            if (!isset($data['chart']['result'][0]['meta']['regularMarketPrice'])) {
                throw new ValuationException(
                    $this->valuation,
                    'Invalid response format from Yahoo Finance API',
                    null,
                    'Invalid response format from Yahoo Finance API'
                );
            }

            return (float) $data['chart']['result'][0]['meta']['regularMarketPrice'];
        } catch (\Exception $e) {
            throw new ValuationException(
                $this->valuation,
                'Failed to fetch price from Yahoo Finance: ' . $e->getMessage(),
                null,
                $e->getMessage()
            );
        }
    }

    public static function getFields(): array
    {
        return [
            'symbol' => TextInput::make('symbol')
                ->label(__('Yahoo Finance symbol'))
                ->required()
                ->helperText('Enter the stock symbol (e.g., AAPL, MSFT, GOOGL)'),
        ];
    }
}
