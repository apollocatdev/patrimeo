<div>
    @if(empty($records))
    <form wire:submit="import">
        {{ $this->form }}

        <div class="pt-12" style="padding-top: 2rem;">
            <div class="flex gap-3">
                <x-filament::button type="submit" disabled="{{ empty($formData['importer_type']) }}">
                    {{ __('Import Data') }}
                </x-filament::button>
            </div>
        </div>
    </form>
    @endif

    @if(!empty($records))
    <div class="space-y-6">
        <div>
            <h1 class="fi-header-heading">{{ __('Data to import') }}</h1>
            <div class="mt-3 p-4 bg-blue-50 border border-blue-200 rounded-lg dark:bg-blue-900/20 dark:border-blue-800">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                            {{ __('Mapping Assets') }}
                        </h3>
                        <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                            <p>{{ __('For each asset, you can map it to an existing or new envelop, asset class, or cotation. Use the dropdown to select an existing item, or enter a new name in the text field.') }}</p>
                            <p class="mt-2">{{ __('Use the "Cascade" buttons to apply the same mapping to all assets with the same envelop, asset class, or cotation name.') }}</p>
                            <p class="mt-2"><strong>{{ __('Important:') }}</strong> {{ __('Each newly created envelop must be associated with an envelop type after import.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Import Data Table -->
        <div class="fi-card relative overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm ring-1 ring-gray-950/5 dark:border-gray-700 dark:bg-gray-900 dark:ring-white/10">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('Data to Import') }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('Select items to import and map their properties') }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                <input type="checkbox"
                                    {{ count($this->getSelectedRecords()) === count($records) && count($records) > 0 ? 'checked' : '' }}
                                    wire:click="setSelectAllProperty($event.target.checked)"
                                    class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                {{ __('Asset Info') }}
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                {{ __('Existing Envelop') }}
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                {{ __('New Envelop') }}
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                {{ __('Cascade Envelop') }}
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                {{ __('Existing Asset Class') }}
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                {{ __('New Asset Class') }}
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                {{ __('Cascade Asset Class') }}
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                {{ __('Existing Valuation') }}
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                {{ __('New Valuation') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($records as $index => $record)
                        <tr class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="px-4 py-4 whitespace-nowrap">
                                <input type="checkbox"
                                    wire:click="toggleRecordSelection({{ $index }})"
                                    {{ $record['is_selected'] ?? false ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </td>
                            <td class="px-4 py-4">
                                <div class="text-sm">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $record['original_data']['name'] ?? '-' }}</div>
                                    <div class="text-gray-500 dark:text-gray-400">
                                        <div>{{ __('Account') }}: {{ $record['original_data']['account_name'] ?? '-' }}</div>
                                        <div>{{ __('Class') }}: {{ $record['original_data']['class'] ?? '-' }}</div>
                                        <div>{{ __('Envelop') }}: {{ $record['original_data']['envelop'] ?? '-' }}</div>
                                        <div>{{ __('Quantity') }}: {{ isset($record['original_data']['quantity']) ? number_format($record['original_data']['quantity'], 2) : '-' }}</div>
                                        <div>{{ __('Currency') }}: {{ $record['original_data']['currency'] ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <select wire:model.live="records.{{ $index }}.mappings.envelop.existing_id"
                                    wire:change="onEnvelopChange({{ $index }}, $event.target.value)"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    <option value="">{{ __('Select envelop...') }}</option>
                                    @foreach($dropdowns['envelops'] as $envelop)
                                    <option value="{{ $envelop->id }}">
                                        {{ $envelop->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <input type="text" wire:model.live="records.{{ $index }}.mappings.envelop.new_name"
                                    wire:change="onEnvelopTextChange({{ $index }}, $event.target.value)"
                                    placeholder="{{ __('New envelop name') }}"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <button wire:click="cascadeEnvelop('{{ $record['original_data']['envelop'] ?? '' }}')"
                                    wire:key="cascade-envelop-{{ $index }}"
                                    {{ empty($record['mappings']['envelop']['existing_id']) && empty($record['mappings']['envelop']['new_name']) ? 'disabled' : ''}}
                                    class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                    {{ __('Cascade') }}
                                </button>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <select wire:model.live="records.{{ $index }}.mappings.asset_class.existing_id"
                                    wire:change="onAssetClassChange({{ $index }}, $event.target.value)"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    <option value="">{{ __('Select asset class...') }}</option>
                                    @foreach($dropdowns['asset_classes'] as $assetClass)
                                    <option value="{{ $assetClass->id }}">
                                        {{ $assetClass->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <input type="text" wire:model.live="records.{{ $index }}.mappings.asset_class.new_name"
                                    wire:change="onAssetClassTextChange({{ $index }}, $event.target.value)"
                                    placeholder="{{ __('New asset class name') }}"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <button wire:click="cascadeAssetClass('{{ $record['original_data']['class'] ?? '' }}')"
                                    wire:key="cascade-asset-class-{{ $index }}"
                                    {{ empty($record['mappings']['asset_class']['existing_id']) && empty($record['mappings']['asset_class']['new_name']) ? 'disabled' : ''}}
                                    class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                    {{ __('Cascade') }}
                                </button>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <select wire:model.live="records.{{ $index }}.mappings.cotation.existing_id"
                                    wire:change="onValuationChange({{ $index }}, $event.target.value)"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    <option value="">{{ __('Select cotation...') }}</option>
                                    @foreach($dropdowns['cotations'] as $cotation)
                                    <option value="{{ $cotation->id }}">
                                        {{ $cotation->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <input type="text" wire:model.live="records.{{ $index }}.mappings.cotation.new_name"
                                    wire:change="onValuationTextChange({{ $index }}, $event.target.value)"
                                    placeholder="{{ __('New cotation name') }}"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ count($this->getSelectedRecords()) }} {{ __('items selected') }}
                    </div>
                    <button wire:click="importSelected"
                        {{ count($this->getSelectedRecords()) === 0 ? 'disabled' : '' }}
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        {{ __('Import Selected') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>