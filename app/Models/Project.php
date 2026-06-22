<?php

namespace App\Models;

use App\Filters\Filterable;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasUuid, HasFactory, Filterable, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'start_date',
        'deadline',
        'user_id',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'deadline'    => 'date',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function (Project $project) {
            if ($project->isForceDeleting()) {
                $project->issues()->withTrashed()->forceDelete();
            } else {
                $project->issues()->each(fn(Issue $issue) => $issue->delete());
            }
        });

        static::restoring(function (Project $project) {
            $project->issues()->onlyTrashed()->each(fn(Issue $issue) => $issue->restore());
        });
    }
}
