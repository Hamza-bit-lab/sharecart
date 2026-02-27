<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Grocery list model (table: lists).
 * PHP reserves "list", so we use GroceryList for the class name.
 */
class GroceryList extends Model
{
    use HasFactory;

    protected $table = 'lists';

    protected $fillable = [
        'user_id',
        'name',
        'due_date',
        'archived_at',
        'invite_token',
        'join_code',
    ];

    public static function generateUniqueJoinCode(): string
    {
        do {
            $code = str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT);
        } while (self::query()->where('join_code', $code)->exists());

        return $code;
    }

    protected $casts = [
        'due_date' => 'date',
        'archived_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    public function ensureInviteToken(): string
    {
        if (empty($this->invite_token)) {
            $this->invite_token = \Illuminate\Support\Str::random(32);
            $this->save();
        }
        return $this->invite_token;
    }

    public function ensureJoinCode(): string
    {
        if (empty($this->join_code)) {
            $this->join_code = self::generateUniqueJoinCode();
            $this->save();
        }
        return $this->join_code;
    }

    /**
     * Owner of the list.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Users with whom this list is shared (excluding owner).
     * Pivot table list_user uses list_id (not grocery_list_id) because our table is "lists".
     */
    public function sharedWith(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'list_user', 'list_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Items in this list (ordered by creation).
     */
    public function items(): HasMany
    {
        return $this->hasMany(ListItem::class, 'list_id')->orderBy('created_at');
    }
}
