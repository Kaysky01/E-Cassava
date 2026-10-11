<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_id',
        'type',
        'title_id',
        'title_en',
        'body_id',
        'body_en',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function title(): string
    {
        return app()->getLocale() === 'en' ? $this->title_en : $this->title_id;
    }

    public function body(): string
    {
        return app()->getLocale() === 'en' ? $this->body_en : $this->body_id;
    }

    public function isRead(): bool
    {
        return ! is_null($this->read_at);
    }
}
