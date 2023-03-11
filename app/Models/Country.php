<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $table = 'countries';

    public function conferences()
    {
        return $this->hasMany(Conference::class, 'country_id', 'id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'country_id', 'id');
    }

    public function __toString()
    {
        return $this->name;
    }

    protected static function getCountry($country_id)
    {
        return self::find($country_id);
    }

    public static function associateCountry($model_object, $country_id)
    {
        $country = self::getCountry($country_id);
        $model_object->country()->associate($country);
        $model_object->save();
    }
}
