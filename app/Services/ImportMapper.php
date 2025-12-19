<?php

namespace App\Services;

use App\Data\ImportRecord;
use App\Models\AssetClass;
use App\Models\Envelop;
use App\Models\Valuation;

class ImportMapper
{
    private array $dropdowns;
    private ?ImporterInterface $importer;

    public function __construct(?ImporterInterface $importer = null)
    {
        $this->importer = $importer;
        $this->dropdowns = [
            'asset_classes' => AssetClass::all(),
            'envelops' => Envelop::all(),
            'valuations' => Valuation::all(),
        ];
    }

    public function mapRecords(array $records): array
    {
        $mappedRecords = [];

        foreach ($records as $record) {
            $mappedRecords[] = $this->mapRecord($record);
        }

        return $mappedRecords;
    }

    private function mapRecord(ImportRecord $record): array
    {
        $mappings = [];

        // Map envelop
        if ($record->envelop) {
            $mappings['envelop'] = $this->mapEnvelop($record->envelop);
        }

        // Map asset class
        if ($record->assetClass) {
            $mappings['asset_class'] = $this->mapAssetClass($record->assetClass);
        }

        // Map valuation
        if ($record->isin || $record->name) {
            $mappings['valuation'] = $this->mapValuation($record);
        }

        return [
            'original_data' => $record->toArray(),
            'mappings' => $mappings,
            'is_selected' => false,
        ];
    }

    private function mapEnvelop(string $envelopName): array
    {
        $existing = $this->dropdowns['envelops']->first(function ($envelop) use ($envelopName) {
            return strtolower($envelop->name) === strtolower($envelopName);
        });

        return [
            'existing_id' => $existing?->id,
            'new_name' => $existing ? '' : $envelopName,
        ];
    }

    private function mapAssetClass(string $className): array
    {
        $existing = $this->dropdowns['asset_classes']->first(function ($assetClass) use ($className) {
            return strtolower($assetClass->name) === strtolower($className);
        });

        return [
            'existing_id' => $existing?->id,
            'new_name' => $existing ? '' : $className,
        ];
    }

    private function mapValuation(ImportRecord $record): array
    {
        $existing = null;

        // Check if this is a cash asset class and should use currency valuation
        if ($this->importer && $record->assetClass && $record->currency) {
            $cashAssetClasses = $this->importer::getCashAssetClasses();
            if (in_array($record->assetClass, $cashAssetClasses)) {
                // For cash assets, try to find or suggest a currency valuation
                $existing = $this->dropdowns['valuations']->first(function ($valuation) use ($record) {
                    return strtolower($valuation->name) === strtolower($record->currency);
                });

                return [
                    'existing_id' => $existing?->id,
                    'new_name' => $existing ? '' : $record->currency,
                ];
            }
        }

        // Original logic for non-cash assets
        // Try by ISIN first
        if ($record->isin) {
            $existing = $this->dropdowns['valuations']->first(function ($valuation) use ($record) {
                return strtolower($valuation->isin ?? '') === strtolower($record->isin);
            });
        }

        // Try by name if not found by ISIN
        if (!$existing && $record->name) {
            $existing = $this->dropdowns['valuations']->first(function ($valuation) use ($record) {
                return strtolower($valuation->name) === strtolower($record->name);
            });
        }

        return [
            'existing_id' => $existing?->id,
            'new_name' => $existing ? '' : ($record->name ?: $record->isin),
        ];
    }

    public function getDropdowns(): array
    {
        return $this->dropdowns;
    }
}
