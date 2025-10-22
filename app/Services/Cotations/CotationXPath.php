<?php

namespace App\Services\Cotations;

use App\Models\Cotation;
use App\Exceptions\CotationException;
use App\Helpers\Currency;
use Illuminate\Support\Facades\Http;
use Filament\Forms\Components\TextInput;
use Symfony\Component\DomCrawler\Crawler;
use App\Services\CotationInterface;

class CotationXPath implements CotationInterface
{
    protected Cotation $cotation;

    private const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36';

    public function __construct(Cotation $cotation)
    {
        $this->cotation = $cotation;
    }

    public function getQuote(): float
    {
        try {
            $url = $this->cotation->update_data['url'] ?? '';
            $xpath = $this->cotation->update_data['xpath'] ?? '';

            if (empty($url)) {
                throw new CotationException(
                    $this->cotation,
                    'URL is required for XPath-based cotation',
                    null,
                    'Missing field: url'
                );
            }

            if (empty($xpath)) {
                throw new CotationException(
                    $this->cotation,
                    'XPath expression is required for XPath-based cotation',
                    null,
                    'Missing field: xpath'
                );
            }

            $request = Http::withUserAgent(self::USER_AGENT)->get($url);

            if (!$request->successful()) {
                throw new CotationException(
                    $this->cotation,
                    'Failed to fetch URL for XPath-based cotation',
                    $request->status(),
                    $request->body()
                );
            }

            $html = $request->body();
            $crawler = new Crawler($html);

            try {
                $result = $crawler->filterXPath($xpath)->text();
                return Currency::sanitizeToFloat($result);
            } catch (\Exception $e) {
                throw new CotationException(
                    $this->cotation,
                    'Failed to extract value using XPath expression',
                    null,
                    $e->getMessage()
                );
            }
        } catch (CotationException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new CotationException(
                $this->cotation,
                'Unexpected error in XPath-based cotation: ' . $e->getMessage(),
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
            'xpath' => TextInput::make('xpath')
                ->label(__('XPath expression')),
        ];
    }
}
