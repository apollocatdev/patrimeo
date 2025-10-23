<?php

namespace App\Services\Transfers;

use App\Models\Asset;
use App\Models\Transfer;
use App\Enums\TransferType;
use App\Services\TransfersInterface;
use App\Exceptions\TransfersException;
use App\Settings\IntegrationsSettings;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Facades\Log;
use ApollocatDev\FilamentSettings\Facades\FilamentSettings;

class TransfersWoob implements TransfersInterface
{
    protected Asset $asset;

    public function __construct(Asset $asset)
    {
        $this->asset = $asset;
    }

    public function getTransfers(): array
    {
        /** @var IntegrationsSettings $settings */
        $settings = FilamentSettings::getSettingForUser(IntegrationsSettings::class, $this->asset->user_id);
        $binaryPath = $settings->weboobPath;
        $accountName = $this->asset->update_data['account_name'] ?? '';
        $transactionCount = $this->asset->update_data['transaction_count'] ?? 20;

        if (empty($accountName)) {
            throw new TransfersException(
                $this->asset,
                'Account name is required for woob transfers',
                null,
            );
        }

        if (empty($binaryPath)) {
            throw new TransfersException(
                $this->asset,
                'Woob binary path is not configured. Please set it in Settings > Various.',
                null,
            );
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
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new TransfersException(
                $this->asset,
                'Woob command failed with return code ' . $returnCode,
                null,
                'Return code: ' . $returnCode . ' | Output: ' . implode("\n", $output)
            );
        }

        if (empty($output)) {
            throw new TransfersException(
                $this->asset,
                'Woob command returned no output',
                null,
                'Command: ' . $command
            );
        }

        try {
            $json = json_decode(implode("\n", $output), true);
        } catch (\Exception $e) {
            throw new TransfersException(
                $this->asset,
                'Invalid JSON format from woob command',
                null,
                $e->getMessage() . ' | Output: ' . implode("\n", $output)
            );
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new TransfersException(
                $this->asset,
                'JSON decode error: ' . json_last_error_msg(),
                null,
                'Output: ' . implode("\n", $output)
            );
        }

        if (!is_array($json)) {
            throw new TransfersException(
                $this->asset,
                'Woob command did not return an array',
                null,
                'Expected array, got: ' . gettype($json)
            );
        }

        // Process and create transfers
        $transfers = [];
        $skippedDuplicates = 0;

        foreach ($json as $transactionData) {
            try {
                $transfer = $this->createTransfer($transactionData);

                // Check for duplicates including label comparison
                if ($this->checkDuplicate($transfer)) {
                    // Both date/quantities and label match, skip this transaction
                    $skippedDuplicates++;
                    continue;
                }

                $transfer->save();
                $transfers[] = $transfer;
            } catch (TransfersException $e) {
                // Re-throw TransfersException as-is
                throw $e;
            } catch (\Exception $e) {
                throw new TransfersException(
                    $this->asset,
                    'Failed to create transfer: ' . $e->getMessage(),
                    null,
                    'Transaction data: ' . json_encode($transactionData)
                );
            }
        }

        // Log skipped duplicates for information
        if ($skippedDuplicates > 0) {
            Log::info("Skipped {$skippedDuplicates} duplicate transactions for asset {$this->asset->name}");
        }

        // Recompute the asset quantity based on the new transfers
        if (!empty($transfers)) {
            $this->asset->computeQuantity();
        }

        return $transfers;
    }

    protected function createTransfer(array $data): Transfer
    {
        // Validate required fields
        if (empty($data['date']) || empty($data['amount']) || empty($data['label'])) {
            throw new TransfersException(
                $this->asset,
                'Missing required transaction fields',
                null,
                'Required: date, amount, label. Got: ' . json_encode($data)
            );
        }

        // Parse amount and determine transfer type
        $amount = (float) $data['amount'];
        $transferType = $amount < 0 ? TransferType::Expense : TransferType::Income;
        $quantity = abs($amount);

        // Parse date (woob returns Y-m-d format)
        try {
            $date = \Carbon\Carbon::createFromFormat('Y-m-d', $data['date']);
        } catch (\Exception $e) {
            throw new TransfersException(
                $this->asset,
                'Invalid date format from woob',
                null,
                'Expected Y-m-d format, got: ' . $data['date']
            );
        }

        // Create transfer record
        $transfer = new Transfer();
        $transfer->type = $transferType;

        if ($transferType === TransferType::Expense) {
            $transfer->source_id = $this->asset->id;
            $transfer->source_quantity = $quantity;
            $transfer->destination_id = null;
            $transfer->destination_quantity = null;
        } else {
            $transfer->source_id = null;
            $transfer->source_quantity = null;
            $transfer->destination_id = $this->asset->id;
            $transfer->destination_quantity = $quantity;
        }

        $transfer->date = $date;
        $transfer->comment = $data['label'];
        $transfer->user_id = $this->asset->user_id;

        return $transfer;
    }

    /**
     * Check if a duplicate transfer already exists, including label comparison
     */
    protected function checkDuplicate(Transfer $transfer): bool
    {
        $query = Transfer::where('source_id', $transfer->source_id)
            ->where('destination_id', $transfer->destination_id)
            ->where('source_quantity', $transfer->source_quantity)
            ->where('destination_quantity', $transfer->destination_quantity)
            ->where('date', $transfer->date)
            ->where('comment', $transfer->comment);

        // If the transfer is already saved, exclude it from duplicate check
        if ($transfer->exists) {
            $query->where('id', '!=', $transfer->id);
        }

        return $query->exists();
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
