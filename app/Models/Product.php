<?php

namespace App\Models;

use App\Support\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'category_uuid',
        'title',
        'price',
        'description',
        'metadata'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_uuid');
    }
}
