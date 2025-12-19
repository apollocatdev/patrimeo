<?php

namespace App\Livewire;

use App\Models\Asset;
use App\Models\Envelop;
// Setting model removed - using filament-typehint-settings
use Livewire\Component;
use App\Enums\Importers;
use App\Models\Valuation;
use App\Models\Currency;
use App\Models\AssetClass;
use Filament\Schemas\Schema;
use App\Services\ImportMapper;
use App\Services\ImporterInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use App\Services\Importers\ImporterFinary;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Concerns\InteractsWithForms;

class SettingsImporter extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $formData = [];
    public ?array $records = [];
    public array $dropdowns = [];
    public array $displayFields = [];
    public array $mappingFields = [];

    public function mount()
    {
        $importerClass = Importers::FINARY->getClass();
        $defaultValues = $importerClass::getDefaultValues();

        // Keep form state scalar; store enum as its backed value
        $this->form->fill(array_merge([
            'importer_type' => Importers::FINARY->value,
        ], $defaultValues));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('importer_type')
                    ->label(__('Importer Type'))
                    ->options(Importers::class)
                    ->live(),

                Grid::make(1)
                    ->schema(function (Get $get): array {
                        $importerType = $get('importer_type');
                        if (!$importerType) {
                            return [];
                        }

                        $importerClass = $get('importer_type')->getClass();

                        return $importerClass::getFields();
                    })
                    ->key('dynamicImporterFields')
            ])
            ->statePath('formData');
    }

    public function render()
    {
        return view('livewire.settings-importer');
    }

    public function import()
    {
        $data = $this->form->getState();

        // importer_type is stored as string; convert to enum
        $importerClass = Importers::from($data['importer_type'])->getClass();
        $importer = new $importerClass($data);

        try {
            $this->initializeRecords($importer);

            Notification::make()
                ->title(__('Import completed successfully'))
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title(__('Import failed'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function initializeRecords($importer)
    {
        $standardizedData = $importer->getStandardizedData();
        $mapper = new ImportMapper($importer);

        $this->records = $mapper->mapRecords($standardizedData);

        // Initialize is_selected property for each record
        foreach ($this->records as $index => $record) {
            $this->records[$index]['is_selected'] = false;
        }

        $this->dropdowns = $mapper->getDropdowns();
        $this->displayFields = $importer::getDisplayFields();
        $this->mappingFields = $importer::getMappingFields();
    }

    // Handle specific field changes
    public function onEnvelopChange($index, $value)
    {
        // If a dropdown value is selected (not empty), clear the text input
        if (!empty($value)) {
            $this->records[$index]['mappings']['envelop']['new_name'] = '';
        }
    }

    public function onEnvelopTextChange($index, $value)
    {
        // If text input has a value, clear the dropdown
        if (!empty($value)) {
            $this->records[$index]['mappings']['envelop']['existing_id'] = '';
        }
    }

    public function onAssetClassChange($index, $value)
    {
        // If a dropdown value is selected (not empty), clear the text input
        if (!empty($value)) {
            $this->records[$index]['mappings']['asset_class']['new_name'] = '';
        }
    }

    public function onAssetClassTextChange($index, $value)
    {
        // If text input has a value, clear the dropdown
        if (!empty($value)) {
            $this->records[$index]['mappings']['asset_class']['existing_id'] = '';
        }
    }

    public function onValuationChange($index, $value)
    {
        // If a dropdown value is selected (not empty), clear the text input
        if (!empty($value)) {
            $this->records[$index]['mappings']['valuation']['new_name'] = '';
        }
    }

    public function onValuationTextChange($index, $value)
    {
        // If text input has a value, clear the dropdown
        if (!empty($value)) {
            $this->records[$index]['mappings']['valuation']['existing_id'] = '';
        }
    }

    public function cascadeEnvelop($envelop)
    {
        $envelopId = null;
        $newEnvelop = '';

        // Find the first record with this envelop to get the mapping
        foreach ($this->records as $index => $record) {
            if ($record['original_data']['envelop'] === $envelop) {
                $envelopId = $record['mappings']['envelop']['existing_id'] ?? null;
                $newEnvelop = $record['mappings']['envelop']['new_name'] ?? '';
                break;
            }
        }

        if (!$envelopId && !$newEnvelop) {
            Notification::make()
                ->title(__('Please select an existing envelop or enter a new one'))
                ->warning()
                ->send();
            return;
        }

        // Apply to all records with the same envelop
        foreach ($this->records as $index => $record) {
            if ($record['original_data']['envelop'] === $envelop) {
                $this->records[$index]['mappings']['envelop']['existing_id'] = $envelopId;
                $this->records[$index]['mappings']['envelop']['new_name'] = $newEnvelop;
            }
        }

        Notification::make()
            ->title(__('Envelop cascaded successfully'))
            ->success()
            ->send();
    }

    public function cascadeAssetClass($class)
    {
        $assetClassId = null;
        $newAssetClass = '';

        // Find the first record with this class to get the mapping
        foreach ($this->records as $index => $record) {
            if ($record['original_data']['class'] === $class) {
                $assetClassId = $record['mappings']['asset_class']['existing_id'] ?? null;
                $newAssetClass = $record['mappings']['asset_class']['new_name'] ?? '';
                break;
            }
        }

        if (!$assetClassId && !$newAssetClass) {
            Notification::make()
                ->title(__('Please select an existing asset class or enter a new one'))
                ->warning()
                ->send();
            return;
        }

        // Apply to all records with the same class
        foreach ($this->records as $index => $record) {
            if ($record['original_data']['class'] === $class) {
                $this->records[$index]['mappings']['asset_class']['existing_id'] = $assetClassId;
                $this->records[$index]['mappings']['asset_class']['new_name'] = $newAssetClass;
            }
        }

        Notification::make()
            ->title(__('Asset class cascaded successfully'))
            ->success()
            ->send();
    }

    public function importSelected()
    {
        $selectedRecords = $this->getSelectedRecords();

        if (empty($selectedRecords)) {
            Notification::make()
                ->title(__('Please select items to import'))
                ->warning()
                ->send();
            return;
        }

        try {
            $importerClass = Importers::from($this->formData['importer_type'])->getClass();
            $importer = new $importerClass($this->formData);

            // Prepare the data with mappings
            $preparedRecords = [];
            foreach ($selectedRecords as $index => $record) {
                $preparedRecords[] = [
                    'data' => $record['original_data'],
                    'asset_class_id' => $record['mappings']['asset_class']['existing_id'] ?? null,
                    'new_asset_class' => $record['mappings']['asset_class']['new_name'] ?? '',
                    'envelop_id' => $record['mappings']['envelop']['existing_id'] ?? null,
                    'new_envelop' => $record['mappings']['envelop']['new_name'] ?? '',
                    'valuation_id' => $record['mappings']['valuation']['existing_id'] ?? null,
                    'new_valuation' => $record['mappings']['valuation']['new_name'] ?? '',
                ];
            }

            // Save the imported data
            $this->saveImport($preparedRecords, $importer);

            Notification::make()
                ->title(__('Selected items imported successfully'))
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title(__('Import failed'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Save the imported data to the database
     */
    protected function saveImport(array $data, $importer): void
    {
        foreach ($data as $item) {
            $record = $item['data'];
            $assetClassId = $item['asset_class_id'];
            $newAssetClass = $item['new_asset_class'] ?? '';
            $envelopId = $item['envelop_id'];
            $newEnvelop = $item['new_envelop'] ?? '';
            $valuationId = $item['valuation_id'] ?? null;
            $newValuation = $item['new_valuation'] ?? '';

            // Create or get envelop
            if ($envelopId) {
                $envelop = Envelop::find($envelopId);
            } elseif ($newEnvelop) {
                $envelop = Envelop::firstOrCreate(
                    ['name' => $newEnvelop, 'user_id' => Auth::user()->id],
                    ['name' => $newEnvelop, 'user_id' => Auth::user()->id]
                );
                $envelopId = $envelop->id;
            } else {
                continue; // Skip if no envelop specified
            }

            // Create or get asset class
            if ($assetClassId) {
                $assetClass = AssetClass::find($assetClassId);
            } elseif ($newAssetClass) {
                $assetClass = AssetClass::firstOrCreate(
                    ['name' => $newAssetClass, 'user_id' => Auth::user()->id],
                    ['name' => $newAssetClass, 'user_id' => Auth::user()->id]
                );
                $assetClassId = $assetClass->id;
            } else {
                continue; // Skip if no asset class specified
            }

            // Create or get valuation if needed
            if ($valuationId) {
                $valuation = Valuation::find($valuationId);
            } elseif ($newValuation) {
                $currencyId = (isset($record['class']) && in_array($record['class'], $importer::getCashAssetClasses()) && !empty($record['currency']))
                    ? Currency::firstOrCreate(['symbol' => $record['currency']], ['symbol' => $record['currency'], 'main' => false])->id
                    : Currency::getDefault()?->id;

                $valuation = Valuation::firstOrCreate(
                    ['name' => $newValuation, 'user_id' => Auth::user()->id],
                    [
                        'name' => $newValuation,
                        'user_id' => Auth::user()->id,
                        'isin' => $record['isin'] ?? null,
                        'symbol' => $record['symbol'] ?? null,
                        'currency_id' => $currencyId,
                    ]
                );
                $valuationId = $valuation->id;
            }

            // Create or update the asset
            Asset::updateOrCreate(
                [
                    'name' => $record['name'],
                    'user_id' => Auth::user()->id,
                ],
                [
                    'envelop_id' => $envelopId,
                    'class_id' => $assetClassId,
                    'valuation_id' => $valuationId,
                    'quantity' => $record['quantity'] ?? null,
                    'value' => $record['current_value'] ?? null,
                    'last_update' => now(),
                ]
            );
        }
    }



    public function getSelectedRecords()
    {
        return array_filter($this->records, function ($record) {
            return $record['is_selected'] ?? false;
        });
    }

    public function setSelectAllProperty($value)
    {
        if ($value) {
            foreach ($this->records as $index => $record) {
                $this->records[$index]['is_selected'] = true;
            }
        } else {
            foreach ($this->records as $index => $record) {
                $this->records[$index]['is_selected'] = false;
            }
        }
    }

    public function toggleRecordSelection($index)
    {
        if (!isset($this->records[$index]['is_selected'])) {
            $this->records[$index]['is_selected'] = false;
        }
        $this->records[$index]['is_selected'] = !$this->records[$index]['is_selected'];
    }
}
