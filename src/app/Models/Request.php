<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Request extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'requested_date',
        'target_date',
        'start_time',
        'end_time',
        'break1_start',
        'break1_end',
        'break2_start',
        'break2_end',
        'note',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }
}
