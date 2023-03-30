<?php

namespace App\Http\Controllers;

use App\Models\Plan;

class PlanController extends Controller
{
    public function index()
    {
        return Plan::all();
    }

    public function show(Plan $plan)
    {
        return $plan;
    }
}
