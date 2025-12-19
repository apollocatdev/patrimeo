<?php

namespace App\Data;

class ImportRecord
{
    public function __construct(
        public string $name,
        public ?string $accountName = null,
        public ?string $assetClass = null,
        public ?string $envelop = null,
        public ?float $quantity = null,
        public ?string $currency = null,
        public ?string $isin = null,
        public ?string $symbol = null,
        public array $originalData = [],
        public array $mappings = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            accountName: $data['account_name'] ?? null,
            assetClass: $data['class'] ?? null,
            envelop: $data['envelop'] ?? null,
            quantity: $data['quantity'] ?? null,
            currency: $data['currency'] ?? null,
            isin: $data['isin'] ?? null,
            symbol: $data['symbol'] ?? null,
            originalData: $data,
            mappings: $data['mappings'] ?? []
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'account_name' => $this->accountName,
            'class' => $this->assetClass,
            'envelop' => $this->envelop,
            'quantity' => $this->quantity,
            'currency' => $this->currency,
            'isin' => $this->isin,
            'symbol' => $this->symbol,
            'original_data' => $this->originalData,
            'mappings' => $this->mappings,
        ];
    }
}
