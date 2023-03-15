<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ZoomMeeting extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = false;

    protected $casts = [
        'start_time' => 'datetime',
        'created_at' => 'datetime'
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}
