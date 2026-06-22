<?php

namespace App\Models;

use App\Filters\Filterable;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasUuid, HasFactory, Filterable, SoftDeletes;

    protected $fillable = [
        'uuid',
        'issue_id',
        'author_name',
        'body',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }
}
