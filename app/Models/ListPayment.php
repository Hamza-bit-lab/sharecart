<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'list_id',
        'user_id',
        'guest_name',
        'amount',
        'currency',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function list(): BelongsTo
    {
        return $this->belongsTo(GroceryList::class, 'list_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
