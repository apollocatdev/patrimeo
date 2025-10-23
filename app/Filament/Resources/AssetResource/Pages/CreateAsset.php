<?php

namespace App\Filament\Resources\AssetResource\Pages;

use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\AssetResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAsset extends CreateRecord
{
    protected static string $resource = AssetResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::user()->id;
        return $data;
    }

    protected function afterCreate(): void
    {
        $this->handleTagAssociations();
    }

    protected function handleTagAssociations(): void
    {
        $asset = $this->record;
        $data = $this->form->getState();

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
