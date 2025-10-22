# Importer Architecture

This document describes the new flexible importer architecture that allows easy addition of new data importers.

## Overview

The new architecture separates concerns and provides a standardized way to handle different data sources:

1. **ImporterInterface** - Defines the contract all importers must follow
2. **ImportRecord** - Standardized data structure for all importers
3. **ImportMapper** - Handles mapping logic for all importers
4. **SettingsImporter** - UI component that works with any importer and handles all database persistence

## Key Components

### ImporterInterface

All importers must implement this interface:

```php
interface ImporterInterface
{
    public function __construct(array $parameters);
    public static function getFields(): array;
    public static function getDefaultValues(): array;
    public function import(): array;
    public function getStandardizedData(): array;
    public static function getMappingFields(): array;
    public static function getDisplayFields(): array;
    public static function getCashAssetClasses(): array;
}
```

### ImportRecord

Standardized data structure that all importers convert their data to:

```php
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
}
```

**Expected Data Structure from `import()` method:**

Each record returned by the `import()` method should contain these fields:

```php
[
    'name' => 'Asset Name',           // Required: Name of the asset
    'account_name' => 'Account Name', // Optional: Name of the account
    'class' => 'Asset Class',         // Required: Asset class (e.g., 'Stocks', 'Cash', 'Bonds')
    'envelop' => 'Envelop Name',      // Required: Envelop/institution name
    'quantity' => 100.0,              // Optional: Quantity of the asset
    'currency' => 'USD',              // Optional: Currency code (e.g., 'USD', 'EUR')
    'isin' => 'ISIN123',              // Optional: ISIN identifier
    'symbol' => 'SYMBOL',             // Optional: Trading symbol
    'current_value' => 1000.0,        // Optional: Current value of the asset
]
```

**Important Notes:**
- The `class` field is used to determine if this is a cash asset (see `getCashAssetClasses()`)
- The `currency` field is required for cash assets to determine the cotation currency
- For non-cash assets, the default currency from the Currency table will be used
- All fields except `name`, `class`, and `envelop` are optional

### ImportMapper

Handles the mapping logic for all importers:

- Auto-detects existing entities (envelops, asset classes, cotations)
- Provides consistent mapping behavior across all importers
- Manages dropdowns and mapping state

## Adding a New Importer

To add a new importer, follow these steps:

1. **Create the importer class**:

```php
class ImporterMyService implements ImporterInterface
{
    public function __construct(array $parameters)
    {
        // Initialize with parameters
    }

    public function import(): array
    {
        // Import data from your service
        return [
            [
                'name' => 'Asset Name',
                'account_name' => 'Account Name',
                'class' => 'Asset Class',
                'envelop' => 'Envelop Name',
                'quantity' => 100.0,
                'currency' => 'USD',
                'isin' => 'ISIN123',
                'symbol' => 'SYMBOL',
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
            'api_key' => TextInput::make('api_key')
                ->label('API Key')
                ->required(),
        ];
    }

    public static function getDefaultValues(): array
    {
        return [
            'api_key' => env('MY_SERVICE_API_KEY', ''),
        ];
    }

    public static function getMappingFields(): array
    {
        return ['envelop', 'asset_class', 'cotation'];
    }

    public static function getDisplayFields(): array
    {
        return ['name', 'account_name', 'class', 'envelop', 'quantity', 'currency', 'isin'];
    }


}
```

2. **Add to the Importers enum**:

```php
enum Importers: string implements HasLabel
{
    case FINARY = 'finary';
    case MY_SERVICE = 'my_service';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::FINARY => __('Finary'),
            self::MY_SERVICE => __('My Service'),
        };
    }

    public function getClass(): ?string
    {
        return match ($this) {
            self::FINARY => ImporterFinary::class,
            self::MY_SERVICE => ImporterMyService::class,
        };
    }
}
```

## Benefits

1. **Separation of Concerns**: Importers only prepare data, SettingsImporter handles all database persistence
2. **Consistency**: All importers use the same mapping logic and data structure
3. **Extensibility**: Easy to add new importers without changing the UI or persistence logic
4. **Maintainability**: Changes to persistence logic happen in one place (SettingsImporter)
5. **Flexibility**: Each importer can define its own fields and display options
6. **Currency Management**: Automatic currency determination based on asset class and centralized default currency handling

## Data Flow

1. User selects importer type
2. Dynamic form fields are loaded based on importer
3. User fills in importer-specific parameters
4. Data is imported and converted to standardized format
5. ImportMapper handles mapping to existing entities
6. User can modify mappings in the UI
7. Data is saved using SettingsImporter's centralized persistence logic

## Example Usage

The UI automatically adapts to any importer:

- Form fields are dynamically generated based on `getFields()`
- Display fields are shown based on `getDisplayFields()`
- Mapping options are available based on `getMappingFields()`
- No changes needed to the UI when adding new importers
- All database persistence is handled centrally by SettingsImporter

## Currency Management

The system automatically determines the currency for newly created cotations:

1. **Cash Assets**: If the asset class is in `getCashAssetClasses()` (e.g., 'Cash', 'Fonds euro'), the currency from the imported data is used
2. **Non-Cash Assets**: The default currency (marked as `main` in the Currency table) is used
3. **Automatic Creation**: Missing currencies for cash assets are automatically created during import
4. **User Scoping**: All currency operations respect the authenticated user's scope 