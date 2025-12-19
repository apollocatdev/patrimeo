<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\In;

class WidgetFilterData extends Data
{
    public function __construct(
        public string $field,

        #[In(['=', '!=', '>', '<', '>=', '<='])]
        public string $operator,

        public string $value,
    ) {}


}
