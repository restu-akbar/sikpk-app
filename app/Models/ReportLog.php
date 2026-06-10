<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ReportLog extends Model
{
    use HasUuids;

    const UPDATED_AT = null;
    protected $fillable = [
        'report_id',
        'progress',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}
