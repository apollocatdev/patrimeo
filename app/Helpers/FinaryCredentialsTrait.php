<?php

namespace App\Helpers;

use App\Settings\IntegrationsSettings;
use ApollocatDev\FilamentSettings\Facades\FilamentSettings;

trait FinaryCredentialsTrait
{
    protected function getFinaryCredentials(int $userId): array
    {
        /** @var IntegrationsSettings $setting */
        $setting = FilamentSettings::getSettingForUser(IntegrationsSettings::class, $userId);

        $sharingLink = '';
        $secureCode = '';

        if ($setting && isset($setting->finarySharingLink, $setting->finarySecureCode)) {
            $sharingLink = $setting->finarySharingLink;
            $secureCode = $setting->finarySecureCode;
        }

        // Check if sharing_link is a URL and extract the ID
        if (filter_var($sharingLink, FILTER_VALIDATE_URL)) {
            $path = parse_url($sharingLink, PHP_URL_PATH);
            if ($path && preg_match('/\/v2\/share\/([a-zA-Z0-9]+)$/', $path, $matches)) {
                $sharingLink = $matches[1];
            }
        }

        return [
            'sharing_link' => $sharingLink,
            'secure_code' => $secureCode,
        ];
    }

    protected function validateFinaryCredentials(int $userId): void
    {
        $credentials = $this->getFinaryCredentials($userId);

        if (empty($credentials['sharing_link']) || empty($credentials['secure_code'])) {
            throw new \Exception('Finary credentials not configured in settings. Please configure Finary sharing link and secure code in Settings > Various');
        }
    }
}
