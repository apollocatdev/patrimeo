<?php

namespace App\Services\Transactions;

use App\Models\Asset;
use App\Models\Transaction;
use App\Enums\TransactionType;
use Illuminate\Support\Facades\Http;
use App\Settings\IntegrationsSettings;
use App\Services\TransactionsInterface;
use Filament\Forms\Components\TextInput;
use App\Exceptions\TransactionsException;
use ApollocatDev\FilamentSettings\Facades\FilamentSettings;

class TransactionsAnkr implements TransactionsInterface
{
    protected Asset $asset;

    protected string $apiKey;
    protected string $walletAddress;
    protected string $contractAddress;

    public function __construct(Asset $asset)
    {
        $this->asset = $asset;
    }

    public function saveTransactions(): void
    {
        /** @var IntegrationsSettings $settings */
        $settings = FilamentSettings::getSettingForUser(IntegrationsSettings::class, $this->asset->user_id);


        if (empty($settings->ankrApiKey)) {
            throw new TransactionsException($this->asset, 'Transaction Ankr API token is not configured. Please set it in Settings > Integrations.', null);
        }

        $this->apiKey = $settings->ankrApiKey;


        $this->walletAddress = $this->asset->update_data['wallet_address'] ?? '';
        $this->contractAddress = $this->asset->update_data['contract_address'] ?? '';

        if (empty($this->walletAddress)) {
            throw new TransactionsException($this->asset, 'Wallet address is required for Ankr Ethereum transactions', null);
        }

        if ($this->contractAddress) {
            $quantity = $this->getBalanceContract($this->walletAddress, $this->contractAddress);
        } else {
            $quantity = $this->getBalance($this->walletAddress);
        }

        $assetQuantity = $this->asset->quantity ?? 0;
        $difference = $quantity - $assetQuantity;

        if ($difference === 0) {
            return;
        }
        if ($difference > 0) {
            Transaction::createTransaction(
                TransactionType::Income,
                now(),
                null,
                $this->asset,
                $difference,
                'Auto-generated from Ankr sync',
                false
            );
        } else {
            Transaction::createTransaction(
                TransactionType::Expense,
                now(),
                $this->asset,
                null,
                abs($difference),
                'Auto-generated from Ankr sync',
                false
            );
        }
    }

    public static function getFields(): array
    {
        return [
            'wallet_address' => TextInput::make('wallet_address')
                ->label(__('Wallet Address'))
                ->helperText(__('The Ethereum wallet address to fetch balance from'))
                ->required(),
            'contract_address' => TextInput::make('contract_address')
                ->label(__('Contract Address'))
                ->helperText(__('Optional: ERC-20 token contract address. Leave empty for native ETH balance')),
        ];
    }



    protected function getBalance(string $address): float
    {
        $url = "https://rpc.ankr.com/eth/{$this->apiKey}";

        $response = Http::asJson()->post($url, [
            'jsonrpc' => '2.0',
            'method' => 'eth_getBalance',
            'params' => [$address],
            'id' => 1,
        ]);

        $hexBalance = $response->json()['result'];
        return $this->hexToTokens($hexBalance);
    }

    protected function getBalanceContract(string $walletAddress, string $aTokenAddress): float
    {
        // Get decimals from the contract
        $decimals = $this->getDecimals($aTokenAddress);

        // Remove 0x prefix if present
        $walletAddress = ltrim($walletAddress, '0x');
        $walletAddress = str_pad($walletAddress, 64, '0', STR_PAD_LEFT);

        // balanceOf(address) function signature: 0x70a08231
        $data = '0x70a08231' . $walletAddress;

        $url = "https://rpc.ankr.com/eth/{$this->apiKey}";

        $response = Http::asJson()->post($url, [
            'jsonrpc' => '2.0',
            'method' => 'eth_call',
            'params' => [
                [
                    'to' => $aTokenAddress,
                    'data' => $data,
                ],
                'latest',
            ],
            'id' => 1,
        ]);

        $hexBalance = $response->json()['result'];
        return $this->hexToTokens($hexBalance, $decimals);
    }

    protected function getDecimals(string $tokenAddress): int
    {
        // decimals() function signature: 0x313ce567
        $data = '0x313ce567';

        $url = "https://rpc.ankr.com/eth/{$this->apiKey}";

        $response = Http::asJson()->post($url, [
            'jsonrpc' => '2.0',
            'method' => 'eth_call',
            'params' => [
                [
                    'to' => $tokenAddress,
                    'data' => $data,
                ],
                'latest',
            ],
            'id' => 1,
        ]);

        $hexDecimals = $response->json()['result'];
        $hexDecimals = ltrim($hexDecimals, '0x');
        return (int) gmp_strval(gmp_init($hexDecimals, 16));
    }

    protected function hexToTokens(string $hexValue, int $decimals = 18): float
    {
        $hexValue = ltrim($hexValue, '0x');
        $wei = gmp_init($hexValue, 16);
        $divisor = gmp_pow(10, $decimals);
        $tokens = gmp_div_q($wei, $divisor);
        $remainder = gmp_mod($wei, $divisor);

        if (gmp_cmp($remainder, 0) === 0) {
            return (float) gmp_strval($tokens);
        }

        $tokensStr = gmp_strval($tokens);
        $remainderStr = str_pad(gmp_strval($remainder), $decimals, '0', STR_PAD_LEFT);
        $remainderStr = rtrim($remainderStr, '0');

        return (float) ($tokensStr . '.' . $remainderStr);
    }

    public static function getNetworksDropdown(): array
    {
        return [
            '0g' => '0G',
            'allora' => 'Allora',
            'aptos' => 'Aptos',
            'arbitrum' => 'Arbitrum',
            'arbitrum_nova' => 'Arbitrum Nova',
            'atleta' => 'Atleta',
            'avail' => 'Avail',
            'avalanche' => 'Avalanche',
            'b2' => 'B2 Network',
            'bahamut' => 'Bahamut',
            'base' => 'Base',
            'bitcoin' => 'Bitcoin',
            'bitlayer' => 'Bitlayer',
            'blast' => 'Blast',
            'bnb' => 'BNB Smart Chain',
            'botanix' => 'Botanix',
            'celo' => 'Celo',
            'chiliz' => 'Chiliz',
            'core' => 'Core',
            'corn' => 'Corn',
            'electroneum' => 'Electroneum',
            'eth' => 'Ethereum',
            'eth_beacon' => 'Ethereum Beacon',
            'etherlink' => 'Etherlink',
            'fantom' => 'Fantom',
            'filecoin' => 'Filecoin',
            'flare' => 'Flare',
            'fuel' => 'Fuel',
            'gnosis' => 'Gnosis',
            'gnosis_beacon' => 'Gnosis Beacon',
            'goat' => 'GOAT',
            'gravity' => 'Gravity',
            'harmony' => 'Harmony',
            'heco' => 'Huobi ECO Chain',
            'horizen_eon' => 'Horizen EON',
            'iota' => 'IOTA',
            'iota_evm' => 'IOTA EVM',
            'iotex' => 'IoTeX',
            'kaia' => 'Kaia',
            'kava' => 'Kava',
            'kinto' => 'Kinto',
            'kusama' => 'Kusama',
            'linea' => 'Linea',
            'mantle' => 'Mantle',
            'matchain' => 'Matchain',
            'metis' => 'Metis',
            'midnight' => 'Midnight',
            'monad' => 'Monad',
            'moonbeam' => 'Moonbeam',
            'movement' => 'Movement',
            'near' => 'NEAR',
            'nervos' => 'Nervos',
            'neura' => 'Neura',
            'optimism' => 'Optimism',
            'polkadot' => 'Polkadot',
            'polygon' => 'Polygon',
            'rollux' => 'Rollux',
            'scroll' => 'Scroll',
            'secret' => 'Secret Network',
            'sei' => 'Sei',
            'solana' => 'Solana',
            'somnia' => 'Somnia',
            'sonic' => 'Sonic',
            'stellar' => 'Stellar',
            'story' => 'Story',
            'sui' => 'Sui',
            'sui_grpc' => 'Sui gRPC',
            'swell' => 'Swell',
            'syscoin' => 'Syscoin',
            'tac' => 'TAC',
            'taiko' => 'Taiko',
            'telos' => 'Telos',
            'tenet' => 'Tenet',
            'ton' => 'TON',
            'tron' => 'TRON',
            'xai' => 'Xai',
            'xdc' => 'XDC Network',
            'x_layer' => 'X Layer',
            'xphere_mainnet' => 'Xphere',
            'xrp_mainnet' => 'XRP',
            'zksync_era' => 'zkSync Era',
        ];
    }
}
