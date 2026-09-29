<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_log';

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForModel(Builder $query, string $type, int $id): Builder
    {
        return $query->where('model_type', $type)->where('model_id', $id);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public static function record(
        string $action,
        ?string $modelType = null,
        ?int $modelId = null,
        array $oldValues = [],
        array $newValues = [],
    ): static {
        $user = auth()->user();
        return static::create([
            'user_id'    => $user?->id,
            'action'     => $action,
            'model_type' => $modelType,
            'model_id'   => $modelId,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
