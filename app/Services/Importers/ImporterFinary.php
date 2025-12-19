<?php

namespace App\Services\Importers;

use App\Models\Asset;
use App\Models\Security;
use App\Models\Taxonomy;
use App\Enums\SecurityTypes;
use App\Services\ImporterInterface;
use App\Data\ImportRecord;
use App\Helpers\FinaryCredentialsTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Filament\Forms\Components\TextInput;
use App\Models\Envelop;
use App\Models\AssetClass;
use App\Models\Valuation;
use App\Models\Currency;

class ImporterFinary implements ImporterInterface
{
    use FinaryCredentialsTrait;

    protected ?string $sharingLink = null;
    protected ?string $secureCode = null;

    protected array $parsedData = [];

    public function __construct(array $parameters)
    {
        // Load credentials from settings instead of parameters
        $userId = Auth::id();
        $this->validateFinaryCredentials($userId);
        $credentials = $this->getFinaryCredentials($userId);

        $this->sharingLink = $credentials['sharing_link'];
        $this->secureCode = $credentials['secure_code'];
    }


    public function import(): array
    {
        $data = [];

        // Investments accounts
        $response = Http::get('https://api.finary.com/users/me/portfolio/investments?period=all&sharing_link_id=' . $this->sharingLink . '&access_code=' . $this->secureCode);
        if ($response->status() !== 200) {
            throw new \Exception('Error ' . $response->status() . ' while retrieving investments accounts');
        }
        $accounts = $response->json()['result']['accounts'];
        foreach ($accounts as $account) {
            $data = array_merge($data, $this->getAccountAssets($account));
        }
        // return $data;

        // Cryptos
        $response = Http::get('https://api.finary.com/users/me/portfolio/cryptos?period=all&sharing_link_id=' . $this->sharingLink . '&access_code=' . $this->secureCode);
        if ($response->status() !== 200) {
            throw new \Exception('Error ' . $response->status() . ' while retrieving cryptos');
        }
        $accounts = $response->json()['result']['accounts'];
        foreach ($accounts as $account) {
            $data = array_merge($data, $this->getAccountAssets($account));
        }
        // return $data;


        // Checking accounts
        $response = Http::get('https://api.finary.com/users/me/portfolio/checking_accounts/accounts?period=all&sharing_link_id=' . $this->sharingLink . '&access_code=' . $this->secureCode);
        if ($response->status() !== 200) {
            throw new \Exception('Error ' . $response->status() . ' while retrieving checking accounts');
        }
        $accounts = $response->json()['result'];
        foreach ($accounts as $account) {
            $data[] = [
                'name' => 'Cash ' . $account['institution']['name'],
                'account_name' => $account['name'],
                'class' => 'Cash',
                'envelop' => $account['institution']['name'],
                'quantity' => $account['balance'],
                'currency' => $account['currency']['code'],
            ];
        }

        // Fonds euros
        $response = Http::get('https://api.finary.com/users/me/portfolio/fonds_euro?period=all&sharing_link_id=' . $this->sharingLink . '&access_code=' . $this->secureCode);
        if ($response->status() !== 200) {
            throw new \Exception('Error ' . $response->status() . ' while retrieving fonds euro');
        }
        $accounts = $response->json()['result']['accounts'];
        foreach ($accounts as $account) {
            $data = array_merge($data, $this->getAccountAssets($account));
        }

        // Metaux precieux
        $response = Http::get('https://api.finary.com/users/me/portfolio/commodities?period=all&sharing_link_id=' . $this->sharingLink . '&access_code=' . $this->secureCode);
        if ($response->status() !== 200) {
            throw new \Exception('Error ' . $response->status() . ' while retrieving fonds euro');
        }
        $accounts = $response->json()['result']['accounts'];
        foreach ($accounts as $account) {
            $data = array_merge($data, $this->getAccountAssets($account));
        }

        // Real-estate
        $response = Http::get('https://api.finary.com/users/me/portfolio/real_estates?period=all&sharing_link_id=' . $this->sharingLink . '&access_code=' . $this->secureCode);
        if ($response->status() !== 200) {
            throw new \Exception('Error ' . $response->status() . ' while retrieving fonds euro');
        }
        $accounts = $response->json()['result']['accounts'];
        foreach ($accounts as $account) {
            $data = array_merge($data, $this->getAccountAssets($account));
        }

        return $data;
    }


    protected function getAccountAssets(array $account): array
    {
        $data = [];
        foreach ($account['fiats'] as $fiat) {
            $data[] = [
                'name' => 'Cash ' . $account['institution']['name'],
                'account_name' => $account['name'],
                'class' => 'Cash',
                'envelop' => $account['institution']['name'],
                'quantity' => $fiat['current_value'],
                'currency' => $fiat['fiat']['code'],
            ];
        }
        foreach ($account['securities'] as $security) {
            $data[] = [
                'account_name' => $account['name'],
                'class' => 'Security',
                'envelop' => $account['institution']['name'],
                'quantity' => $security['quantity'],
                'isin' => $security['security']['isin'],
                'symbol' => $security['security']['symbol'],
                'name' => $security['security']['name'],
                'currency' => $security['security']['currency']['code'],
            ];
        }
        foreach ($account['cryptos'] as $crypto) {
            $data[] = [
                'account_name' => $account['name'],
                'class' => 'Crypto',
                'envelop' => $account['institution']['name'],
                'quantity' => $crypto['quantity'],
                'symbol' => $crypto['crypto']['code'],
                'name' => $crypto['crypto']['name'],
                'currency' => $crypto['crypto']['code'],
            ];
        }

        foreach ($account['fonds_euro'] as $fond) {
            $data[] = [
                'account_name' => $account['name'],
                'class' => 'Fonds euro',
                'envelop' => $account['institution']['name'],
                'quantity' => $fond['current_value'],
                'currency' => $fond['currency']['code'],
                'name' => $fond['name']
            ];
        }
        foreach ($account['precious_metals'] as $metal) {
            $data[] = [
                'account_name' => $account['name'],
                'class' => 'Precious metal',
                'envelop' => $account['institution']['name'],
                'quantity' => $metal['quantity'],
                'currency' => $metal['currency']['code'],
                'name' => $metal['precious_metal']['name']
            ];
        }
        foreach ($account['scpis'] as $scpi) {
            $data[] = [
                'account_name' => $account['name'],
                'class' => 'SCPI',
                'envelop' => $account['institution']['name'],
                'quantity' => $scpi['shares'],
                'name' => $scpi['scpi']['name'],
            ];
        }

        return $data;
    }



    public static function getFields(): array
    {
        return [
            // No fields needed - credentials are loaded from settings
        ];
    }

    public static function getDefaultValues(): array
    {
        return [
            // No default values needed - credentials are loaded from settings
        ];
    }




    public function getStandardizedData(): array
    {
        $importedData = $this->import();
        $standardized = [];

        foreach ($importedData as $record) {
            $standardized[] = ImportRecord::fromArray($record);
        }

        return $standardized;
    }

    public static function getMappingFields(): array
    {
        return ['envelop', 'asset_class', 'valuation'];
    }

    public static function getDisplayFields(): array
    {
        return ['name', 'account_name', 'class', 'envelop', 'quantity', 'currency', 'isin'];
    }

    public static function getCashAssetClasses(): array
    {
        return ['Cash', 'Fonds euro'];
    }
}
