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
        \exec($command, $output, $returnCode);

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

        // Determine transaction type and quantities
        if ($difference > 0) {
            // Balance is higher than asset quantity - income transaction
            $transactionType = TransactionType::Income;
            $destinationQuantity = $difference;
            $sourceQuantity = null;
        } else {
            // Balance is lower than asset quantity - expense transaction
            $transactionType = TransactionType::Expense;
            $sourceQuantity = abs($difference);
            $destinationQuantity = null;
        }

        try {
            $currentDateTime = now();

            $transaction = new Transaction();
            $transaction->type = $transactionType;
            $transaction->source_id = $transactionType === TransactionType::Expense ? $this->asset->id : null;
            $transaction->source_quantity = $sourceQuantity;
            $transaction->destination_id = $transactionType === TransactionType::Income ? $this->asset->id : null;
            $transaction->destination_quantity = $destinationQuantity;
            $transaction->date = $currentDateTime;
            $transaction->comment = 'Auto-generated from simple balance command';
            $transaction->user_id = $this->asset->user_id;

            $transaction->save();

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
