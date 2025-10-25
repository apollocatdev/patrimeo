<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use App\Services\Valuations\ValuationYahoo;
use App\Services\Valuations\ValuationXPath;
use App\Services\Valuations\ValuationCss;
use App\Services\Valuations\ValuationCommand;
use App\Services\Valuations\ValuationCoingecko;
use App\Services\Valuations\ValuationXPathJavascript;
use App\Services\Valuations\ValuationOpenAI;

enum ValuationUpdateMethod: string implements HasLabel
{
    case YAHOO = 'yahoo';
    case XPATH = 'xpath';
    case XPATH_JAVASCRIPT = 'xpath_javascript';
    case CSS = 'css';
    case COINGECKO = 'coingecko';
    case OPENAI = 'openai';
    case FIXED = 'fixed';
    case MANUAL = 'manual';
    case COMMAND = 'command';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::YAHOO => __('Yahoo Finance'),
            self::XPATH => __('XPath'),
            self::XPATH_JAVASCRIPT => __('XPath with JavaScript'),
            self::CSS => __('CSS selector'),
            self::COINGECKO => __('Coingecko'),
            self::OPENAI => __('OpenAI ChatGPT'),
            self::FIXED => __('Fixed'),
            self::MANUAL => __('Manual'),
            self::COMMAND => __('Command'),
        };
    }

    public function getServiceClass(): ?string
    {
        return match ($this) {
            self::YAHOO => ValuationYahoo::class,
            self::XPATH => ValuationXPath::class,
            self::XPATH_JAVASCRIPT => ValuationXPathJavascript::class,
            self::CSS => ValuationCss::class,
            self::COINGECKO => ValuationCoingecko::class,
            self::OPENAI => ValuationOpenAI::class,
            self::COMMAND => ValuationCommand::class,
            self::FIXED, self::MANUAL => null,
        };
    }

    public function getRateLimiterKey(?array $updateData = null): string
    {
        return match ($this) {
            self::YAHOO => 'yahoo',
            self::XPATH => $updateData && isset($updateData['url'])
                ? parse_url($updateData['url'], PHP_URL_HOST)
                : 'none',
            self::OPENAI => 'openai',
            self::XPATH_JAVASCRIPT, self::CSS, self::COINGECKO, self::FIXED, self::MANUAL, self::COMMAND => 'none',
        };
    }

    public static function dropdown()
    {
        $dropdown = [];
        foreach (self::cases() as $case) {
            $dropdown[] = ['label' => $case->getLabel(), 'value' => $case->value];
        }
        return $dropdown;
    }
}
