<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use App\Services\Cotations\CotationYahoo;
use App\Services\Cotations\CotationXPath;
use App\Services\Cotations\CotationCss;
use App\Services\Cotations\CotationCommand;
use App\Services\Cotations\CotationCoingecko;
use App\Services\Cotations\CotationXPathJavascript;
use App\Services\Cotations\CotationOpenAI;

enum CotationUpdateMethod: string implements HasLabel
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
            self::YAHOO => CotationYahoo::class,
            self::XPATH => CotationXPath::class,
            self::XPATH_JAVASCRIPT => CotationXPathJavascript::class,
            self::CSS => CotationCss::class,
            self::COINGECKO => CotationCoingecko::class,
            self::OPENAI => CotationOpenAI::class,
            self::COMMAND => CotationCommand::class,
            self::FIXED, self::MANUAL => null,
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
