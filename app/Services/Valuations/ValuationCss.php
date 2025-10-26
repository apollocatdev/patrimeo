<?php

namespace App\Services\Valuations;

use App\Models\Valuation;
use App\Exceptions\ValuationException;
use App\Helpers\Currency;
use Illuminate\Support\Facades\Http;
use Filament\Forms\Components\TextInput;
use Symfony\Component\DomCrawler\Crawler;
use App\Services\ValuationInterface;

class ValuationCss implements ValuationInterface
{
    protected Valuation $valuation;

    private const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36';

    public function __construct(Valuation $valuation)
    {
        $this->valuation = $valuation;
    }

    public function getQuote(): float
    {
        try {
            $url = $this->valuation->update_data['url'] ?? '';
            $selector = $this->valuation->update_data['selector'] ?? '';

            if (empty($url)) {
                throw new ValuationException(
                    $this->valuation,
                    'URL is required for CSS-based valuation',
                    null,
                    'Missing field: url'
                );
            }

            if (empty($selector)) {
                throw new ValuationException(
                    $this->valuation,
                    'CSS selector is required for CSS-based valuation',
                    null,
                    'Missing field: selector'
                );
            }

            $request = Http::withUserAgent(self::USER_AGENT)->get($url);

            if (!$request->successful()) {
                throw new ValuationException(
                    $this->valuation,
                    'Failed to fetch URL for CSS-based valuation',
                    $request->status(),
                    $request->body()
                );
            }

            $html = $request->body();
            $crawler = new Crawler($html);

            try {
                $result = $crawler->filter($selector)->text();
                return Currency::sanitizeToFloat($result);
            } catch (\Exception $e) {
                throw new ValuationException(
                    $this->valuation,
                    'Failed to extract value using CSS selector',
                    null,
                    $e->getMessage()
                );
            }
        } catch (ValuationException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ValuationException(
                $this->valuation,
                'Unexpected error in CSS-based valuation: ' . $e->getMessage(),
                null,
                $e->getMessage()
            );
        }
    }

    public static function getFields(): array
    {
        return [
            'url' => TextInput::make('url')
                ->label(__('URL')),
            'selector' => TextInput::make('selector')
                ->label(__('CSS selector')),
        ];
    }
}
