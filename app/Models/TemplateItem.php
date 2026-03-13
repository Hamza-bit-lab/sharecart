<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Template;

class TemplateItem extends Model
{
    use HasFactory;

    protected $fillable = ['template_id', 'name', 'quantity'];

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }
}
