<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'list_item_id',
        'user_id',
        'guest_name',
        'message',
    ];

    public function listItem(): BelongsTo
    {
        return $this->belongsTo(ListItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
