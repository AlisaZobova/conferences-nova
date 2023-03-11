<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        return Category::with('children', 'parents')->get();
    }

    public function store(CategoryRequest $request)
    {
        $data = $request->validated();
        $category = Category::create($data);
        return $category->load('children', 'parents');
    }

    public function show(Category $category)
    {
        return $category->load('children', 'parents', 'reports', 'conferences');
    }

    public function update(Category $category, CategoryRequest $request)
    {
        $data = $request->validated();
        $category->update($data);
        return $category->load('children', 'parents');
    }

    public function destroy(Category $category)
    {
        $category->delete();
    }
}
