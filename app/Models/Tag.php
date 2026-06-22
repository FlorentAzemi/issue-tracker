<?php

namespace App\Models;

use App\Filters\Filterable;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
    use HasUuid, HasFactory, Filterable, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'color',
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

    public function issues(): BelongsToMany
    {
        return $this->belongsToMany(Issue::class);
    }
}
