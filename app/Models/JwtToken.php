<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JwtToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'unique_id',
        'token_title',
        'restrictions',
        'permissions',
        'expires_at',
        'last_used_at',
        'refreshed_at',
    ];

    protected function casts(): array
    {
        return [
            'restrictions' => 'array',
            'permissions' => 'array',
            'expires_at' => 'datetime',
            'last_used_at' => 'datetime',
            'refreshed_at' => 'datetime'
        ];
    }

    public static function isValidToken(string $tokenId)
    {
        return JwtToken::where('unique_id', $tokenId)
            ->where(function($query) {
                $query->whereNull('expires_at');
                $query->orWhere('expires_at', '>=', now());
            })->exists();
    }
}
