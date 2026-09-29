<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    use HasFactory;

    protected $table = 'system_logs';

    protected $fillable = [
        'level',
        'channel',
        'message',
        'context',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
        ];
    }

    public function scopeLevel(Builder $query, string $level): Builder
    {
        return $query->where('level', $level);
    }

    public function scopeChannel(Builder $query, string $channel): Builder
    {
        return $query->where('channel', $channel);
    }

    public static function record(string $level, string $channel, string $message, array $context = []): static
    {
        return static::create([
            'level'   => $level,
            'channel' => $channel,
            'message' => $message,
            'context' => $context ?: null,
        ]);
    }
}
