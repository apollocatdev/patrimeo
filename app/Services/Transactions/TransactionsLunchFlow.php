<?php

namespace App\Services\Transactions;

use App\Models\Asset;
use App\Models\Transaction;
use App\Enums\TransactionType;
use App\Services\TransactionsInterface;
use App\Exceptions\TransactionsException;
use App\Settings\IntegrationsSettings;
use ApollocatDev\FilamentSettings\Facades\FilamentSettings;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Http;
use App\Helpers\Logs\LogTransactions;

class TransactionsLunchFlow implements TransactionsInterface
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
        $apiToken = $settings->lunchflowApiToken;
        $accountName = $this->asset->update_data['account_name'] ?? '';

        if (empty($apiToken)) {
            throw new TransactionsException($this->asset, 'Lunch Flow API token is not configured. Please set it in Settings > Integrations.', null);
        }

        if (empty($accountName)) {
            throw new TransactionsException($this->asset, 'Account name is required for Lunch Flow transactions', null);
        }

        // Step 1: Get accounts list and find the account by name
        $accountsResponse = Http::withHeaders([
            'x-api-key' => $apiToken,
        ])->get('https://www.lunchflow.app/api/v1/accounts');

        if (!$accountsResponse->successful()) {
            throw new TransactionsException(
                $this->asset,
                'Failed to fetch accounts from Lunch Flow API',
                null,
                'HTTP Status: ' . $accountsResponse->status() . ' | Response: ' . $accountsResponse->body()
            );
        }

        $accountsData = $accountsResponse->json();
        if (!isset($accountsData['accounts']) || !is_array($accountsData['accounts'])) {
            throw new TransactionsException(
                $this->asset,
                'Invalid response format from Lunch Flow API (accounts)',
                null,
                'Response: ' . json_encode($accountsData)
            );
        }

        // Find account by name
        $accountId = null;
        foreach ($accountsData['accounts'] as $account) {
            if (isset($account['name']) && $account['name'] === $accountName) {
                $accountId = $account['id'] ?? null;
                break;
            }
        }

        if ($accountId === null) {
            throw new TransactionsException(
                $this->asset,
                "Account with name '{$accountName}' not found in Lunch Flow",
                null,
                'Available accounts: ' . json_encode(array_column($accountsData['accounts'], 'name'))
            );
        }

        // Step 2: Get transactions for the account
        $transactionsResponse = Http::withHeaders([
            'x-api-key' => $apiToken,
        ])->get("https://www.lunchflow.app/api/v1/accounts/{$accountId}/transactions");

        if (!$transactionsResponse->successful()) {
            throw new TransactionsException(
                $this->asset,
                'Failed to fetch transactions from Lunch Flow API',
                null,
                'HTTP Status: ' . $transactionsResponse->status() . ' | Response: ' . $transactionsResponse->body()
            );
        }

        $transactionsData = $transactionsResponse->json();
        if (!isset($transactionsData['transactions']) || !is_array($transactionsData['transactions'])) {
            throw new TransactionsException(
                $this->asset,
                'Invalid response format from Lunch Flow API (transactions)',
                null,
                'Response: ' . json_encode($transactionsData)
            );
        }

        // Process and create transactions
        $skippedDuplicates = 0;

        foreach ($transactionsData['transactions'] as $transactionData) {
            try {
                $transaction = $this->createTransaction($transactionData);

                // Check for duplicates
                if ($transaction->checkDuplicate()) {
                    $skippedDuplicates++;
                    continue;
                }

                $transaction->save();
            } catch (TransactionsException $e) {
                throw $e;
            } catch (\Exception $e) {
                throw new TransactionsException(
                    $this->asset,
                    'Failed to create transaction: ' . $e->getMessage(),
                    null,
                    'Transaction data: ' . json_encode($transactionData)
                );
            }
        }

        if ($skippedDuplicates > 0) {
            LogTransactions::info("Skipped {$skippedDuplicates} duplicate transactions for asset {$this->asset->name}");
        }
    }

    protected function createTransaction(array $data): Transaction
    {
        // Validate required fields
        if (empty($data['date']) || !isset($data['amount'])) {
            throw new TransactionsException(
                $this->asset,
                'Missing required transaction fields',
                null,
                'Required: date, amount. Got: ' . json_encode($data)
            );
        }

        // Parse amount and determine transaction type
        $amount = (float) $data['amount'];
        $transactionType = $amount < 0 ? TransactionType::Expense : TransactionType::Income;
        $quantity = abs($amount);

        // Parse date (Lunch Flow returns Y-m-d format)
        try {
            $date = \Carbon\Carbon::createFromFormat('Y-m-d', $data['date']);
        } catch (\Exception $e) {
            throw new TransactionsException(
                $this->asset,
                'Invalid date format from Lunch Flow',
                null,
                'Expected Y-m-d format, got: ' . $data['date']
            );
        }

        // Build label from merchant and description
        $label = trim(($data['merchant'] ?? '') . ' ' . ($data['description'] ?? ''));
        if (empty($label)) {
            $label = 'Lunch Flow Transaction';
        }

        // Create transaction record
        // For expenses: source is the asset, destination is null
        // For income: source is null, destination is the asset
        $source = $transactionType === TransactionType::Expense ? $this->asset : null;
        $destination = $transactionType === TransactionType::Income ? $this->asset : null;

        $transaction = Transaction::createTransaction(
            $transactionType,
            $date,
            $source,
            $destination,
            $quantity,
            $label,
            false
        );

        return $transaction;
    }

    public static function getFields(): array
    {
        return [
            'account_name' => TextInput::make('account_name')
                ->label(__('Account Name'))
                ->helperText(__('The account name is the same as the one displayed in the dashboard of Lunch Flow'))
                ->required(),
        ];
    }
}
