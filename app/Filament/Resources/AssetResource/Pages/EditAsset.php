<?php

namespace App\Filament\Resources\AssetResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\AssetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\TaxonomyTag;

class EditAsset extends EditRecord
{
    protected static string $resource = AssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $asset = $this->record;

        // Load existing tag associations
        $existingTags = $asset->tags()->withPivot('weight')->get();

        // Prepare data for non-weighted taxonomies
        $taxonomyTags = [];
        foreach ($existingTags as $tag) {
            if (!$tag->taxonomy->weighted) {
                $taxonomyTags[$tag->taxonomy_id] = $tag->id;
            }
        }
        $data['taxonomy_tags'] = $taxonomyTags;

        // Prepare data for weighted taxonomies
        $weightedTags = [];
        foreach ($existingTags as $tag) {
            if ($tag->taxonomy->weighted && $tag->pivot->weight) {
                $weightedTags[$tag->id] = $tag->pivot->weight;
            }
        }
        $data['weighted_tags'] = $weightedTags;

        return $data;
    }

    protected function afterSave(): void
    {
        $this->handleTagAssociations();
    }

    protected function handleTagAssociations(): void
    {
        $asset = $this->record;
        $data = $this->form->getState();

        // Clear existing tag associations
        $asset->tags()->detach();

        // Handle non-weighted taxonomy tags
        if (isset($data['taxonomy_tags'])) {
            foreach ($data['taxonomy_tags'] as $taxonomyId => $tagId) {
                if ($tagId) {
                    $asset->tags()->attach($tagId);
                }
            }
        }

        // Handle weighted taxonomy tags
        if (isset($data['weighted_tags'])) {
            foreach ($data['weighted_tags'] as $tagId => $weight) {
                if ($weight !== null && $weight > 0) {
                    $asset->tags()->attach($tagId, ['weight' => $weight]);
                }
            }
        }
    }
}
