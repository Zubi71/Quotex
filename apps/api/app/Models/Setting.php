<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'is_admin_only',
    ];

    protected function casts(): array
    {
        return [
            'is_admin_only' => 'boolean',
        ];
    }

    // ─── Static Helpers ────────────────────────────────────────────────────

    /**
     * Get a typed setting value with cache.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting:{$key}", 3600, function () use ($key, $default): mixed {
            $setting = static::where('key', $key)->first();
            if ($setting === null) {
                return $default;
            }
            return $setting->typedValue();
        });
    }

    /**
     * Set a setting value and bust cache.
     */
    public static function set(string $key, mixed $value): void
    {
        $setting = static::where('key', $key)->first();
        if ($setting) {
            $setting->update(['value' => is_array($value) ? json_encode($value) : (string) $value]);
        } else {
            static::create([
                'key'   => $key,
                'value' => is_array($value) ? json_encode($value) : (string) $value,
                'type'  => is_int($value) ? 'integer' : (is_float($value) ? 'float' : (is_bool($value) ? 'boolean' : (is_array($value) ? 'json' : 'string'))),
                'label' => $key,
            ]);
        }
        Cache::forget("setting:{$key}");
    }

    /**
     * Get all settings grouped.
     */
    public static function allGrouped(): array
    {
        $settings = static::all();
        $grouped  = [];
        foreach ($settings as $setting) {
            $grouped[$setting->group][] = [
                'key'         => $setting->key,
                'value'       => $setting->typedValue(),
                'type'        => $setting->type,
                'label'       => $setting->label,
                'description' => $setting->description,
            ];
        }
        return $grouped;
    }

    // ─── Scopes ────────────────────────────────────────────────────────────

    public function scopeGroup(Builder $query, string $group): Builder
    {
        return $query->where('group', $group);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_admin_only', false);
    }

    // ─── Helpers ───────────────────────────────────────────────────────────

    public function typedValue(): mixed
    {
        return match ($this->type) {
            'integer' => (int) $this->value,
            'float'   => (float) $this->value,
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'json'    => json_decode($this->value, true),
            default   => $this->value,
        };
    }
}
