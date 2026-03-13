<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single item on a grocery list.
 */
class ListItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'list_id',
        'name',
        'quantity',
        'completed',
        'completed_by',
        'completed_at',
        'completed_by_name',
        'position',
        'claimed_by_user_id',
        'claimed_by_name',
        'claimed_at',
    ];

    protected $casts = [
        'completed' => 'boolean',
        'completed_at' => 'datetime',
        'claimed_at' => 'datetime',
    ];

    /**
     * The list this item belongs to.
     */
    public function groceryList(): BelongsTo
    {
        return $this->belongsTo(GroceryList::class, 'list_id');
    }

    /**
     * The user who marked this item as purchased/completed (if any).
     */
    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * The user who claimed this item (if any).
     */
    public function claimedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by_user_id');
    }
}
