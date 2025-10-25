<?php

namespace App\Services\Transactions;

use App\Models\Asset;
use App\Models\Transaction;
use App\Enums\TransactionType;
use App\Services\TransactionsInterface;
use App\Exceptions\TransactionsException;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Log;

class TransactionsCommandSimpleBalance implements TransactionsInterface
{
    protected Asset $asset;

    public function __construct(Asset $asset)
    {
        $this->asset = $asset;
    }

    public function saveTransactions(): void
    {
        $command = $this->asset->update_data['command'] ?? '';

        if (empty($command)) {
            throw new TransactionsException($this->asset, 'Command parameter is missing required for simple balance transactions', null);
        }

        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new TransactionsException($this->asset, 'Command failed with return code ' . $returnCode, null, 'Return code: ' . $returnCode . ' | Output: ' . implode("\n", $output));
        }

        if (empty($output)) {
            throw new TransactionsException($this->asset, 'Command returned no output', null, 'Expected a numeric value representing the current balance');
        }

        // Get the first line of output and convert to float
        $balanceString = trim($output[0]);

        if (!is_numeric($balanceString)) {
            throw new TransactionsException($this->asset, 'Command output is not numeric', null, 'Expected numeric value, got: ' . $balanceString);
        }

        $currentBalance = (float) $balanceString;
        $assetQuantity = $this->asset->quantity ?? 0;
        $difference = $currentBalance - $assetQuantity;

        // If no difference, no transaction needed
        if (abs($difference) < 0.01) {
            return;
        }

        // Determine transfer type and quantities
        if ($difference > 0) {
            // Balance is higher than asset quantity - income transfer
            $transferType = TransactionType::Income;
            $destinationQuantity = $difference;
            $sourceQuantity = null;
        } else {
            // Balance is lower than asset quantity - expense transfer
            $transferType = TransactionType::Expense;
            $sourceQuantity = abs($difference);
            $destinationQuantity = null;
        }

        try {
            $currentDateTime = now();

            $transfer = new Transaction();
            $transfer->type = $transferType;
            $transfer->source_id = $transferType === TransactionType::Expense ? $this->asset->id : null;
            $transfer->source_quantity = $sourceQuantity;
            $transfer->destination_id = $transferType === TransactionType::Income ? $this->asset->id : null;
            $transfer->destination_quantity = $destinationQuantity;
            $transfer->date = $currentDateTime;
            $transfer->comment = 'Auto-generated from simple balance command';
            $transfer->user_id = $this->asset->user_id;

            $transfer->save();

            // Compute the new quantity based on transactions
            $this->asset->computeQuantity();
        } catch (\Exception $e) {
            throw new TransactionsException($this->asset, 'Failed to create transaction: ' . $e->getMessage(), null, 'Balance: ' . $currentBalance . ', Asset quantity: ' . $assetQuantity . ', Difference: ' . $difference);
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
