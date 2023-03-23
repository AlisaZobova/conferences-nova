<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Http\Requests\NovaRequest;
use Zobova\CategoriesTree\CategoriesTree;

class Category extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Category>
     */
    public static $model = \App\Models\Category::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name'
    ];

    public static $with = ['parent', 'children'];

    public static $group = 'Conferences';

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),
            Text::make('Name')->rules('required'),
            BelongsTo::make('Parent', 'parent', Category::class)
                ->withoutTrashed()
                ->nullable(),
            Number::make('Children', function () {
                $count = 0;
                $children = $this->children;

                while(count($children) > 0){
                    $nextChildren = [];
                    foreach ($children as $child) {
                        $count += 1;
                        $nextChildren = array_merge($nextChildren, $child->children->all());
                    }
                    $children = $nextChildren;
                }

                return $count;
            })->onlyOnIndex(),
            CategoriesTree::make('Children')
                ->withMeta(['tree' => ['root' => true, 'children' => $this->children]])
                ->onlyOnDetail()
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [];
    }

    public static function relatableCategories(NovaRequest $request, $query)
    {
        $ids = $request->get('resourceId') ? [$request->get('resourceId'),] : [];
        $category = \App\Models\Category::find($request->get('resourceId'));
        $children = $category ? $category->children : [];

        while(count($children) > 0){
            $nextChildren = [];
            foreach ($children as $child) {
                array_push($ids, $child['id']);
                $nextChildren = array_merge($nextChildren, $child->children->all());
            }
            $children = $nextChildren;
        }

        return $query->whereNotIn('id', $ids);

    }
}
