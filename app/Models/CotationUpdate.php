<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Support\Facades\Auth;

#[ScopedBy([UserScope::class])]
class CotationUpdate extends Model
{
    protected $fillable = ['date', 'status', 'message', 'cotation_id', 'value', 'user_id', 'http_status_code', 'error_details'];

    protected $casts = [
        'date' => 'datetime',
        'error_details' => 'array',
    ];

    public function cotation()
    {
        return $this->belongsTo(Cotation::class);
    }

    public static function getFailedCotationUpdatesCount(): int
    {
        return self::where('user_id', Auth::id())
            ->where('status', 'error')
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('cotation_updates')
                    ->where('user_id', Auth::id())
                    ->groupBy('cotation_id');
            })
            ->count();
    }

    public static function getFailedCotationUpdates(): Collection
    {
        return self::where('user_id', Auth::id())
            ->where('status', 'error')
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('cotation_updates')
                    ->where('user_id', Auth::id())
                    ->groupBy('cotation_id');
            })
            ->with('cotation')
            ->orderBy('date', 'desc')
            ->get();
    }
}
