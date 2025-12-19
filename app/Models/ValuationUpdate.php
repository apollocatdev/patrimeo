<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Support\Facades\Auth;

#[ScopedBy([UserScope::class])]
class ValuationUpdate extends Model
{
    protected $fillable = ['date', 'status', 'message', 'valuation_id', 'value', 'user_id', 'http_status_code', 'error_details'];

    protected $casts = [
        'date' => 'datetime',
        'error_details' => 'array',
    ];

    public function valuation()
    {
        return $this->belongsTo(Valuation::class);
    }

    public static function getFailedValuationUpdatesCount(): int
    {
        return self::where('user_id', Auth::id())
            ->where('status', 'error')
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('valuation_updates')
                    ->where('user_id', Auth::id())
                    ->groupBy('valuation_id');
            })
            ->count();
    }

    public static function getFailedValuationUpdates(): Collection
    {
        return self::where('user_id', Auth::id())
            ->where('status', 'error')
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('valuation_updates')
                    ->where('user_id', Auth::id())
                    ->groupBy('valuation_id');
            })
            ->with('valuation')
            ->orderBy('date', 'desc')
            ->get();
    }
}
