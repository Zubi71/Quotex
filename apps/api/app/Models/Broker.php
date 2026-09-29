<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Broker extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'adapter_class',
        'is_active',
        'is_mock',
        'config',
        'last_connected_at',
        'connection_status',
    ];

    protected function casts(): array
    {
        return [
            'is_active'          => 'boolean',
            'is_mock'            => 'boolean',
            'config'             => 'array',
            'last_connected_at'  => 'datetime',
        ];
    }

    // ─── Relationships ─────────────────────────────────────────────────────

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    // ─── Scopes ────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeMock(Builder $query): Builder
    {
        return $query->where('is_mock', true);
    }

    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_mock', false);
    }

    // ─── Helpers ───────────────────────────────────────────────────────────

    public function isConnected(): bool
    {
        return $this->connection_status === 'connected';
    }

    public function markConnected(): void
    {
        $this->update([
            'connection_status'  => 'connected',
            'last_connected_at'  => now(),
        ]);
    }

    public function markDisconnected(string $reason = ''): void
    {
        $this->update(['connection_status' => 'disconnected']);
    }
}
