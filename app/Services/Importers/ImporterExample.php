<?php

namespace App\Services\Importers;

use App\Services\ImporterInterface;
use App\Data\ImportRecord;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;

class ImporterExample implements ImporterInterface
{
    protected ?string $csvFile = null;

    public function __construct(array $parameters)
    {
        $this->csvFile = $parameters['csv_file'] ?? null;
    }

    public function import(): array
    {
        // This is just an example - in a real implementation, you would parse the CSV file
        return [
            [
                'name' => 'Example Stock',
                'account_name' => 'Broker Account',
                'class' => 'Stocks',
                'envelop' => 'Investment Account',
                'quantity' => 100.0,
                'currency' => 'USD',
                'isin' => 'US1234567890',
                'symbol' => 'EXMP',
            ],
            [
                'name' => 'Example Bond',
                'account_name' => 'Broker Account',
                'class' => 'Bonds',
                'envelop' => 'Investment Account',
                'quantity' => 50.0,
                'currency' => 'EUR',
                'isin' => 'EU0987654321',
                'symbol' => 'BOND',
            ],
        ];
    }

    public function getStandardizedData(): array
    {
        $importedData = $this->import();
        $standardized = [];

        foreach ($importedData as $record) {
            $standardized[] = ImportRecord::fromArray($record);
        }

        return $standardized;
    }

    public static function getFields(): array
    {
        return [
            'csv_file' => FileUpload::make('csv_file')
                ->label(__('CSV File'))
                ->required()
                ->acceptedFileTypes(['text/csv']),
        ];
    }

    public static function getMappingFields(): array
    {
        return ['envelop', 'asset_class', 'valuation'];
    }

    public static function getDisplayFields(): array
    {
        return ['name', 'account_name', 'class', 'envelop', 'quantity', 'currency', 'isin'];
    }

    public static function getDefaultValues(): array
    {
        return [
            'csv_file' => '',
        ];
    }

    public static function getCashAssetClasses(): array
    {
        return ['Cash', 'Money Market'];
    }
}
