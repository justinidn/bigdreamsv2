<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasMasterActivityLog;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

#[Table('m_company', key: 'id', incrementing: true)]
#[Fillable([
    'id',
    'company_code',
    'company_name',

])]
#[WithoutTimestamps]

class Company extends Model
{
    use HasFactory, HasMasterActivityLog;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    // protected function casts(): array
    // {
    //     return [
    //         'is_active' => 'boolean',
    //     ];
    // }

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

    public function transaction_daily(): BelongsTo
    {
        return $this->belongsTo(TransactionDaily::class, 'company_id');
    }

    public function transaction_hourly(): BelongsTo
    {
        return $this->belongsTo(TransactionHourly::class, 'company_id');
    }
}
