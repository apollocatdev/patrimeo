<?php

namespace App\Services\Transactions;

use App\Models\Asset;
use App\Models\Transaction;
use App\Enums\TransactionType;
use Illuminate\Support\Facades\Log;
use App\Helpers\Logs\LogTransactions;
use Filament\Forms\Components\Toggle;
use App\Settings\IntegrationsSettings;
use App\Services\TransactionsInterface;
use Filament\Forms\Components\TextInput;
use App\Exceptions\TransactionsException;
use ApollocatDev\FilamentSettings\Facades\FilamentSettings;

class TransactionsWoob implements TransactionsInterface
{
    protected Asset $asset;

    public function __construct(Asset $asset)
    {
        $this->asset = $asset;
    }

    public function saveTransactions(): void
    {
        /** @var IntegrationsSettings $settings */
        $settings = FilamentSettings::getSettingForUser(IntegrationsSettings::class, $this->asset->user_id);
        $binaryPath = $settings->weboobPath;
        $accountName = $this->asset->update_data['account_name'] ?? '';
        $transactionCount = $this->asset->update_data['transaction_count'] ?? 20;

        if (empty($accountName)) {
            throw new TransactionsException($this->asset, 'Account name is required for woob transactions', null);
        }

        if (empty($binaryPath)) {
            throw new TransactionsException($this->asset, 'Woob binary path is not configured. Please set it in Settings > Various.', null);
        }

        // Build the woob command
        $command = sprintf(
            '%s bank history -n %d %s -f json',
            escapeshellarg($binaryPath),
            (int) $transactionCount,
            escapeshellarg($accountName)
        );

        $output = [];
        $returnCode = 0;
        \exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new TransactionsException($this->asset, 'Woob command failed with return code ' . $returnCode, null, 'Return code: ' . $returnCode . ' | Output: ' . implode("\n", $output));
        }

        if (empty($output)) {
            throw new TransactionsException($this->asset, 'Woob command returned no output', null, 'Command: ' . $command);
        }

        try {
            $json = json_decode(implode("\n", $output), true);
        } catch (\Exception $e) {
            throw new TransactionsException($this->asset, 'Invalid JSON format from woob command', null, $e->getMessage() . ' | Output: ' . implode("\n", $output));
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new TransactionsException($this->asset, 'JSON decode error: ' . json_last_error_msg(), null, 'Output: ' . implode("\n", $output));
        }

        if (!is_array($json)) {
            throw new TransactionsException($this->asset, 'Woob command did not return an array', null, 'Expected array, got: ' . gettype($json));
        }

        // Process and create transactions
        $skippedDuplicates = 0;

        foreach ($json as $transactionData) {
            try {
                $transaction = $this->createTransaction($transactionData);

                // Check for duplicates including label comparison
                if ($transaction->checkDuplicate()) {
                    // Both date/quantities and label match, skip this transaction
                    $skippedDuplicates++;
                    continue;
                }

                $transaction->save();
            } catch (TransactionsException $e) {
                // Re-throw TransactionsException as-is
                throw $e;
            } catch (\Exception $e) {
                throw new TransactionsException($this->asset, 'Failed to create transaction: ' . $e->getMessage(), null, 'Transaction data: ' . json_encode($transactionData));
            }
        }

        // Log skipped duplicates for information
        if ($skippedDuplicates > 0) {
            LogTransactions::info("Skipped {$skippedDuplicates} duplicate transactions for asset {$this->asset->name}");
        }
    }

    protected function createTransaction(array $data): Transaction
    {
        // Validate required fields
        if (empty($data['date']) || empty($data['amount']) || empty($data['label'])) {
            throw new TransactionsException($this->asset, 'Missing required transaction fields', null, 'Required: date, amount, label. Got: ' . json_encode($data));
        }

        // Parse amount and determine transaction type
        $amount = (float) $data['amount'];
        $transactionType = $amount < 0 ? TransactionType::Expense : TransactionType::Income;
        $quantity = abs($amount);

        // Parse date (woob returns Y-m-d format)
        try {
            $date = \Carbon\Carbon::createFromFormat('Y-m-d', $data['date']);
        } catch (\Exception $e) {
            throw new TransactionsException($this->asset, 'Invalid date format from woob', null, 'Expected Y-m-d format, got: ' . $data['date']);
        }
        // Create transaction record
        $transaction = Transaction::createTransaction($transactionType, $date, $this->asset, null, $quantity, $data['label'], false);
        return $transaction;
    }

    public static function getFields(): array
    {
        return [
            'account_name' => TextInput::make('account_name')
                ->label(__('Account Name'))
                ->helperText(__('Account identifier for woob (e.g., 005981201T@lcl)'))
                ->required(),
            'transaction_count' => TextInput::make('transaction_count')
                ->label(__('Number of Transactions'))
                ->helperText(__('Number of transactions to retrieve (default: 20)'))
                ->numeric()
                ->default(20)
                ->minValue(1)
                ->maxValue(100),
        ];
    }
}
