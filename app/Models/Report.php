<?php

namespace App\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $guarded = false;
    protected $with = ['category'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function conference()
    {
        return $this->belongsTo(Conference::class, 'conference_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'report_id', 'id');
    }

    public function meeting()
    {
        return $this->hasOne(ZoomMeeting::class);
    }
}
