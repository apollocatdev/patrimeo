<div @if($shouldPoll) wire:poll.2s="refreshStatus" @endif>
    <x-filament::button
        color="gray"
        size="sm"
        outlined
        wire:click="updateValuations"
        wire:loading.attr="disabled"
        :disabled="$status['status'] === 'updating' || ! $isIntegrityValid"
        class="{{ $status['status'] === 'updating' ? 'icon-rotating' : '' }}"
        icon="heroicon-m-arrow-path">
        {{ $status['labelButton'] }}
    </x-filament::button>

    <style>
        .icon-rotating svg {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }
    </style>
</div>