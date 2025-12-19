<?php

namespace App\Services\Transactions;

use App\Models\Asset;
use App\Models\Transaction;
use App\Enums\TransactionType;
use App\Services\TransactionsInterface;
use App\Exceptions\TransactionsException;
use App\Helpers\FinaryCredentialsTrait;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TransactionsFinary implements TransactionsInterface
{
    use FinaryCredentialsTrait;

    protected Asset $asset;

    public function __construct(Asset $asset)
    {
        $this->asset = $asset;
    }

    public function saveTransactions(): void
    {
        $objectId = $this->asset->update_data['object_id'] ?? '';
        $assetType = $this->asset->update_data['asset_type'] ?? '';

        if (empty($objectId) || empty($assetType)) {
            throw new TransactionsException($this->asset, 'Object ID and asset type are required for Finary transactions', null, 'Object ID: ' . $objectId . ', Asset Type: ' . $assetType);
        }

        // Validate Finary credentials
        $this->validateFinaryCredentials($this->asset->user_id);
        $credentials = $this->getFinaryCredentials($this->asset->user_id);

        $finaryQuantity = $this->getFinaryQuantity($assetType, $objectId, $credentials);

        if ($finaryQuantity === null) {
            throw new TransactionsException($this->asset, 'Could not retrieve quantity from Finary for the specified object', null, 'Object ID: ' . $objectId . ', Asset Type: ' . $assetType);
        }

        $assetQuantity = $this->asset->quantity ?? 0;
        $difference = $finaryQuantity - $assetQuantity;

        // If no difference, no transaction needed
        if (abs($difference) < 0.01) {
            return;
        }

        // Determine transaction type and quantities
        if ($difference > 0) {
            // Finary quantity is higher than asset quantity - income transaction
            $transactionType = TransactionType::Income;
            $destinationQuantity = $difference;
            $sourceQuantity = null;
        } else {
            // Finary quantity is lower than asset quantity - expense transaction
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
            $transaction->comment = 'Auto-generated from Finary sync';
            $transaction->user_id = $this->asset->user_id;

            $transaction->save();

            // Compute the new quantity based on transactions
            $this->asset->computeQuantity();
        } catch (\Exception $e) {
            throw new TransactionsException($this->asset, 'Failed to create transaction: ' . $e->getMessage(), null, 'Finary quantity: ' . $finaryQuantity . ', Asset quantity: ' . $assetQuantity . ', Difference: ' . $difference);
        }
    }

    protected function getFinaryQuantity(string $assetType, string $objectId, array $credentials): ?float
    {
        $url = $this->getFinaryUrl($assetType);

        if (!$url) {
            throw new TransactionsException($this->asset, 'Invalid asset type for Finary transactions', null, 'Asset type: ' . $assetType);
        }

        $response = Http::get($url . '&sharing_link_id=' . $credentials['sharing_link'] . '&access_code=' . $credentials['secure_code']);

        if ($response->status() !== 200) {
            throw new TransactionsException($this->asset, 'Failed to retrieve data from Finary API', null, 'HTTP status: ' . $response->status() . ' | URL: ' . $url);
        }

        $data = $response->json();

        if (!isset($data['result'])) {
            throw new TransactionsException($this->asset, 'Invalid response format from Finary API', null, 'Response: ' . json_encode($data));
        }

        return $this->extractQuantityFromResponse($assetType, $data['result'], $objectId);
    }

    protected function getFinaryUrl(string $assetType): ?string
    {
        return match ($assetType) {
            'checking_accounts' => 'https://api.finary.com/users/me/portfolio/checking_accounts/accounts?period=all',
            'investments' => 'https://api.finary.com/users/me/portfolio/investments?period=all',
            'fonds_euro' => 'https://api.finary.com/users/me/portfolio/fonds_euro?period=all',
            'commodities' => 'https://api.finary.com/users/me/portfolio/commodities?period=all',
            'real_estates' => 'https://api.finary.com/users/me/portfolio/real_estates?period=all',
            'cryptos' => 'https://api.finary.com/users/me/portfolio/cryptos?period=all',
            default => null,
        };
    }

    protected function extractQuantityFromResponse(string $assetType, array $result, string $objectId): ?float
    {
        if ($assetType === 'checking_accounts') {
            // Special case: checking_accounts has a different structure
            foreach ($result as $account) {
                if ($account['id'] === $objectId) {
                    return (float) $account['balance'];
                }
            }
        } else {
            // Other asset types have accounts structure
            foreach ($result['accounts'] as $account) {
                $quantity = $this->findQuantityInAccount($account, $assetType, $objectId);
                if ($quantity !== null) {
                    return $quantity;
                }
            }
        }

        return null;
    }

    protected function findQuantityInAccount(array $account, string $assetType, string $objectId): ?float
    {
        // Check fiats (cash)
        foreach ($account['fiats'] as $fiat) {
            if ($fiat['id'] === $objectId) {
                return (float) $fiat['current_value'];
            }
        }

        // Check securities
        foreach ($account['securities'] as $security) {
            if ($security['id'] === $objectId) {
                return (float) $security['quantity'];
            }
        }

        // Check cryptos
        foreach ($account['cryptos'] as $crypto) {
            if ($crypto['id'] === $objectId) {
                return (float) $crypto['quantity'];
            }
        }

        // Check fonds euro
        foreach ($account['fonds_euro'] as $fond) {
            if ($fond['id'] === $objectId) {
                return (float) $fond['current_value'];
            }
        }

        // Check precious metals
        foreach ($account['precious_metals'] as $metal) {
            if ($metal['id'] === $objectId) {
                return (float) $metal['quantity'];
            }
        }

        // Check SCPIs
        foreach ($account['scpis'] as $scpi) {
            if ($scpi['id'] === $objectId) {
                return (float) $scpi['shares'];
            }
        }

        return null;
    }

    public static function getFields(): array
    {
        return [
            'object_id' => TextInput::make('object_id')
                ->label(__('Object ID'))
                ->helperText(__('The Finary object ID to retrieve the quantity from'))
                ->required(),

            'asset_type' => Select::make('asset_type')
                ->label(__('Asset Type'))
                ->options([
                    'checking_accounts' => __('Checking Accounts'),
                    'investments' => __('Investments'),
                    'fonds_euro' => __('Fonds Euro'),
                    'commodities' => __('Commodities'),
                    'real_estates' => __('Real Estates'),
                    'cryptos' => __('Cryptocurrencies'),
                ])
                ->helperText(__('The type of asset to sync from Finary'))
                ->required(),
        ];
    }
}
