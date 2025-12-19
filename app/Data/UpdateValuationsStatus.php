<?php

namespace App\Data;


use Illuminate\Support\Facades\Cache;

class UpdateValuationsStatus
{

    public static function get(int $userId): array
    {
        $cacheKey = "user:{$userId}:valuation_update_status";
        return Cache::get($cacheKey, []);
    }

    public static function init(int $userId): array
    {
        $cacheKey = "user:{$userId}:valuation_update_status";
        if (! Cache::has($cacheKey)) {
            $data = [
                'status' => 'idle',
                'labelButton' => __('Valuations'),
                'progress' => [
                    'total' => 0,
                    'done' => 0,
                ],
            ];
            Cache::put($cacheKey, $data);
            return $data;
        }
        return self::get($userId);
    }

    public static function update(int $userId, string $status): array
    {
        $cacheKey = "user:{$userId}:valuation_update_status";
        $data = self::get($userId);

        // Initialize data structure if empty
        if (empty($data)) {
            $data = [
                'status' => 'idle',
                'labelButton' => __('Valuations'),
                'progress' => [
                    'total' => 0,
                    'done' => 0,
                ],
            ];
        }

        $data['status'] = $status;
        if ($status === 'updating') {
            $data['progress']['done'] = 0;
            $data['progress']['total'] = 0;
            $data['labelButton'] = $data['progress']['done'] . ' / ' . $data['progress']['total'];
        } else {
            $data['labelButton'] = __('Valuations');
        }
        Cache::put($cacheKey, $data);
        return $data;
    }

    public static function updateProgress(int $userId, int $total, int $done): array
    {
        $cacheKey = "user:{$userId}:valuation_update_status";
        $data = self::get($userId);

        // Initialize data structure if empty
        if (empty($data)) {
            $data = [
                'status' => 'idle',
                'labelButton' => __('Valuations'),
                'progress' => [
                    'total' => 0,
                    'done' => 0,
                ],
            ];
        }

        $data['progress']['total'] = $total;
        $data['progress']['done'] = $done;
        $data['labelButton'] = $data['progress']['done'] . ' / ' . $data['progress']['total'];

        Cache::put($cacheKey, $data);
        return $data;
    }
}
