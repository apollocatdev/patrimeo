<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class WidgetStatFormatterData extends Data
{
    public function __construct(
        public bool $currency = false,
        public ?int $decimals = 2,
        public ?string $prefix = null,
        public ?string $suffix = null,
    ) {}
}
