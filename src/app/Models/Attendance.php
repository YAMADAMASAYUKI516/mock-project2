<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'start_time',
        'end_time',
        'break1_start',
        'break1_end',
        'break2_start',
        'break2_end',
        'total_work_time',
        'note',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'break1_start' => 'datetime',
        'break1_end' => 'datetime',
        'break2_start' => 'datetime',
        'break2_end' => 'datetime',
        'work_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function request(): HasOne
    {
        return $this->hasOne(Request::class);
    }

    public function getBreakTimeFormattedAttribute()
    {
        if ($this->break1_start && $this->break1_end) {
            $totalMinutes = $this->break1_end->diffInMinutes($this->break1_start);
            if ($this->break2_start && $this->break2_end) {
                $totalMinutes += $this->break2_end->diffInMinutes($this->break2_start);
            }
            return sprintf('%d:%02d', floor($totalMinutes / 60), $totalMinutes % 60);
        }
        return '-';
    }

    public function getTotalTimeFormattedAttribute()
    {
        if ($this->start_time && $this->end_time) {
            $totalMinutes = $this->end_time->diffInMinutes($this->start_time);

            if ($this->break1_start && $this->break1_end) {
                $totalMinutes -= $this->break1_end->diffInMinutes($this->break1_start);
            }
            if ($this->break2_start && $this->break2_end) {
                $totalMinutes -= $this->break2_end->diffInMinutes($this->break2_start);
            }

            return sprintf('%d:%02d', floor($totalMinutes / 60), $totalMinutes % 60);
        }
        return '-';
    }
}
