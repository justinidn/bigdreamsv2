<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasMasterActivityLog;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'name',
    'guard_name',
    'created_by',
    'updated_by',
])]
class Role extends Model
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
