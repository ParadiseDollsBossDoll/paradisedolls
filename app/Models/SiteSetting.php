<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $table      = 'site_settings';
    protected $primaryKey = 'key';
    public $incrementing  = false;
    protected $keyType    = 'string';
    protected $fillable   = ['key', 'value'];
    protected $casts      = ['value' => 'array'];

    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            return Cache::remember("site_setting.{$key}", 300, function () use ($key, $default) {
                return static::find($key)?->value ?? $default;
            });
        } catch (\Throwable) {
            return $default;
        }
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("site_setting.{$key}");
    }
}
