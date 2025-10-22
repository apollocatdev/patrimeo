<?php

namespace App\Services\Cotations;

use App\Models\Cotation;
use App\Exceptions\CotationException;
use App\Helpers\Currency;
use Filament\Forms\Components\TextInput;
use Spatie\Browsershot\Browsershot;
use App\Services\CotationInterface;

class CotationXPathJavascript implements CotationInterface
{
    protected Cotation $cotation;

    public function __construct(Cotation $cotation)
    {
        $this->cotation = $cotation;
    }

    public function getQuote(): float
    {
        try {
            $url = $this->cotation->update_data['url'] ?? '';
            $xpath = $this->cotation->update_data['xpath'] ?? '';
            $waitTime = $this->cotation->update_data['wait_time'] ?? 2000;

            if (empty($url) || empty($xpath)) {
                throw new CotationException(
                    $this->cotation,
                    'URL and XPath are required for JavaScript-based cotation',
                    null,
                    'Missing required fields'
                );
            }

            $html = Browsershot::url($url)
                // ->noSandbox()
                ->timeout(max(30, intval($waitTime / 1000)))
                ->setChromePath(config('custom.chrome_path', '/usr/bin/chromium'))
                ->addChromiumArguments([
                    'no-sandbox',
                    'disable-setuid-sandbox',
                    'disable-dev-shm-usage',
                    'headless=new',
                ])
                ->bodyHtml();

            // Parse with DomCrawler
            $crawler = new \Symfony\Component\DomCrawler\Crawler($html);
            $result = $crawler->filterXPath($xpath)->text();

            return Currency::sanitizeToFloat($result);
        } catch (\Exception $e) {
            throw new CotationException(
                $this->cotation,
                'Unexpected error in JavaScript-based cotation: ' . $e->getMessage(),
                null,
                $e->getMessage()
            );
        }
    }

    public static function getFields(): array
    {
        return [
            'url' => TextInput::make('url')
                ->label(__('URL'))
                ->required(),
            'xpath' => TextInput::make('xpath')
                ->label(__('XPath expression'))
                ->required(),
            'wait_time' => TextInput::make('wait_time')
                ->label(__('Wait time (ms)'))
                ->numeric()
                ->default(2000)
                ->helperText('Time to wait for JavaScript to render'),
        ];
    }
}
