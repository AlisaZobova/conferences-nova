<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dyrynda\Database\Support\CascadeSoftDeletes;

class Category extends Model
{
    use HasFactory;
    use SoftDeletes, CascadeSoftDeletes;

    protected $cascadeDeletes = ['children'];
    protected $dates = ['deleted_at'];
    protected $appends = ['path'];
    protected $fillable = ['name', 'ancestor_id'];

    public function reports()
    {
        return $this->hasMany(Report::class, 'category_id');
    }

    public function conferences()
    {
        return $this->hasMany(Conference::class, 'category_id');
    }

    // One level child
    public function child()
    {
        return $this->hasMany(Category::class, 'ancestor_id');
    }

    // Recursive children
    public function children()
    {
        return $this->hasMany(Category::class, 'ancestor_id')
            ->with('children');
    }

    // One level parent
    public function parent()
    {
        return $this->belongsTo(Category::class, 'ancestor_id');
    }

    // Recursive parents
    public function parents()
    {
        return $this->belongsTo(Category::class, 'ancestor_id')
            ->with('parent');
    }

    public function getPathAttribute()
    {
        $path = [];
        if ($this->ancestor_id) {
            $parent = $this->parent;
            $parent_path = $parent->path;
            $path = array_merge($path, $parent_path);
        }
        $path[] = $this->name;
        return $path;
    }
}
