<?php

namespace App\Services\Transfers;

use App\Models\Asset;
use App\Models\Transfer;
use App\Enums\TransferType;
use App\Services\TransfersInterface;
use App\Exceptions\TransfersException;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Log;

class TransfersCommandSimpleBalance implements TransfersInterface
{
    protected Asset $asset;

    public function __construct(Asset $asset)
    {
        $this->asset = $asset;
    }

    public function getTransfers(): array
    {
        $command = $this->asset->update_data['command'] ?? '';

        if (empty($command)) {
            throw new TransfersException(
                $this->asset,
                'Command parameter is missing required for simple balance transfers',
                null,
            );
        }

        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new TransfersException(
                $this->asset,
                'Command failed with return code ' . $returnCode,
                null,
                'Return code: ' . $returnCode . ' | Output: ' . implode("\n", $output)
            );
        }

        if (empty($output)) {
            throw new TransfersException(
                $this->asset,
                'Command returned no output',
                null,
                'Expected a numeric value representing the current balance'
            );
        }

        // Get the first line of output and convert to float
        $balanceString = trim($output[0]);

        if (!is_numeric($balanceString)) {
            throw new TransfersException(
                $this->asset,
                'Command output is not numeric',
                null,
                'Expected numeric value, got: ' . $balanceString
            );
        }

        $currentBalance = (float) $balanceString;
        $assetQuantity = $this->asset->quantity ?? 0;
        $difference = $currentBalance - $assetQuantity;

        // If no difference, no transfer needed
        if (abs($difference) < 0.01) {
            return [];
        }

        // Determine transfer type and quantities
        if ($difference > 0) {
            // Balance is higher than asset quantity - income transfer
            $transferType = TransferType::Income;
            $destinationQuantity = $difference;
            $sourceQuantity = null;
        } else {
            // Balance is lower than asset quantity - expense transfer
            $transferType = TransferType::Expense;
            $sourceQuantity = abs($difference);
            $destinationQuantity = null;
        }

        try {
            $currentDateTime = now();

            $transfer = new Transfer();
            $transfer->type = $transferType;
            $transfer->source_id = $transferType === TransferType::Expense ? $this->asset->id : null;
            $transfer->source_quantity = $sourceQuantity;
            $transfer->destination_id = $transferType === TransferType::Income ? $this->asset->id : null;
            $transfer->destination_quantity = $destinationQuantity;
            $transfer->date = $currentDateTime;
            $transfer->comment = 'Auto-generated from simple balance command';
            $transfer->user_id = $this->asset->user_id;

            $transfer->save();

            // Compute the new quantity based on transfers
            $this->asset->computeQuantity();

            return [$transfer];
        } catch (\Exception $e) {
            throw new TransfersException(
                $this->asset,
                'Failed to create transfer: ' . $e->getMessage(),
                null,
                'Balance: ' . $currentBalance . ', Asset quantity: ' . $assetQuantity . ', Difference: ' . $difference
            );
        }
    }

    public static function getFields(): array
    {
        return [
            'command' => TextInput::make('command')
                ->label(__('Command'))
                ->helperText(__('Command that returns a simple numeric balance value')),
        ];
    }
}
