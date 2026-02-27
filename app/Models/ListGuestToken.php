<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ListGuestToken extends Model
{
    protected $fillable = ['list_id', 'token_hash', 'expires_at', 'display_name'];

    protected $casts = ['expires_at' => 'datetime'];

    public function list(): BelongsTo
    {
        return $this->belongsTo(GroceryList::class, 'list_id');
    }

    public static function createToken(GroceryList $list, int $ttlDays = 30, ?string $displayName = null): string
    {
        $plain = Str::random(64);
        $hash = hash('sha256', $plain);

        self::create([
            'list_id' => $list->id,
            'token_hash' => $hash,
            'expires_at' => now()->addDays($ttlDays),
            'display_name' => $displayName,
        ]);

        return $plain;
    }

    public static function findValidByPlainToken(string $plain): ?self
    {
        $plain = trim($plain);
        if ($plain === '') {
            return null;
        }
        $hash = hash('sha256', $plain);

        return self::query()
            ->where('token_hash', $hash)
            ->where('expires_at', '>', now())
            ->first();
    }
}
