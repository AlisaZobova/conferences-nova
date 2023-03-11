<?php

namespace App\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Period\Boundaries;
use Spatie\Period\Period;
use Spatie\Period\PeriodCollection;
use Spatie\Period\Precision;

class Conference extends Model
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;

    protected $cascadeDeletes = ['reports'];
    protected $table = 'conferences';
    protected $guarded = false;
    protected $appends = ['available'];

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'conference_id', 'id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function getAvailableAttribute()
    {
        $boundaries = new PeriodCollection(
            Period::make(
                date('Y-m-d', strtotime($this->conf_date)) .
                ' 08:00:00', date('Y-m-d', strtotime($this->conf_date)) . ' 20:00:00',
                Precision::MINUTE(), Boundaries::EXCLUDE_END()
            )
        );
        $periods = new PeriodCollection();
        $reports = Report::where('conference_id', $this->id)->get();

        foreach ($reports as $report) {
            $periods = $periods->add(Period::make($report->start_time, $report->end_time, Precision::MINUTE()));
        }

        return !$boundaries->subtract($periods)->isEmpty();
    }
}
