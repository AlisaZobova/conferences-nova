<?php

namespace App\Observers;

use App\Models\Category;

class CategoryObserver
{
    public function deleted(Category $category): void
    {
        $category->conferences()->each(
            function ($conference) {
                $conference->category()->dissociate();
                $conference->save();
            }
        );
        $category->reports()->each(
            function ($report) {
                $report->category()->dissociate();
                $report->save();
            }
        );
    }
}
