<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasMasterActivityLog;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

#[Fillable([
    'name',
    'display_name',
    'created_by',
    'updated_by',
])]
class MasterMenu extends Model
{
    use HasFactory, HasMasterActivityLog;

    public const array PERMISSION_ACTIONS = ['view', 'create', 'update', 'delete', 'export'];

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
     * Get dropdown options for active records.
     *
     * @return array<int, string>
     */
    // Uncomment jika ingin membuat dropdown
    //  public static function getDropdownOptions(): array
    // {
    //   return static::where('is_active', true)
    //     ->orderBy('id', 'asc')
    //    ->pluck('name', 'id')
    //    ->toArray();
    // }

    /**
     * Get the user who created the record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the record.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
