<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasMasterActivityLog;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('t_stock_daily', key: 'id', incrementing: true)]
#[Fillable([
    'company_id', // Sesuaikan kolom fillable sesuai kebutuhan tabel Anda
    'open',
    'close',
    'high',
    'low',
    'volume',
    'date',
])]
class TransactionDaily extends Model
{
    use HasFactory, HasMasterActivityLog;

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
