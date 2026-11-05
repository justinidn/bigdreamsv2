<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasMasterActivityLog;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

#[Fillable([
    'department_name',
    'department_alias',
    'is_active',
    'created_by',
    'updated_by',
])]
class Department extends Model
{
    use HasFactory, HasMasterActivityLog;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Model $model): void {
            if (Auth::check() && empty($model->created_by)) {
                $model->created_by = Auth::id();
            }
        });

        static::updating(function (Model $model): void {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }

    /**
     * Get dropdown options for active departments.
     *
     * @return array<int, string>
     */
    public static function getDropdownOptions(): array
    {
        return static::where('is_active', true)
            ->orderBy('department_name', 'asc')
            ->pluck('department_name', 'id')
            ->toArray();
    }

    /**
     * Get the user who created the department.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the department.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the users belonging to the department.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'department_id');
    }
}
