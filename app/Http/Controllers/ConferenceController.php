<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConferenceRequest;
use App\Jobs\ProcessConferenceListenersExport;
use App\Jobs\ProcessConferencesExport;
use App\Models\Conference;
use App\Models\Country;
use App\Models\User;
use Illuminate\Http\Request;

class ConferenceController extends Controller
{
    public function index(Request $request)
    {
        return $this->getFilteredConferences($request)->orderBy('conf_date', 'DESC')->paginate(15);
    }

    public function getFilteredConferences(Request $request)
    {
        $conferences = Conference::with('country', 'reports', 'category');

        $count = $request->query('page') ? 1 : 0;

        if (count($request->query()) > $count) {
            $conferences->whereDate('conf_date', '>=', date("Y-m-d"));
        }

        foreach ($request->query() as $key => $value) {
            if ($key === 'from') {
                $conferences->whereDate('conf_date', '>=', $value);
            }
            if ($key === 'to') {
                $conferences->whereDate('conf_date', '<=', $value);
            }
            if ($key === 'reports') {
                $range = explode('-', $value);
                $conferences->withCount('reports')->
                having('reports_count', '>=', $range[0], 'and')->
                having('reports_count', '<=', $range[1]);
            }
            if ($key === 'category') {
                $categories = explode(',', $value);
                $conferences->whereIn('category_id', $categories);
            }
        }

        return $conferences;
    }

    public function search(Request $request)
    {
        if ($request->query('title')) {
            $conferences = Conference::whereRaw("UPPER(title) LIKE '%" . strtoupper($request->query('title')) . "%'")
                ->whereDate('conf_date', '>=', date("Y-m-d"));
            return $conferences->orderBy('conf_date', 'DESC')->get();
        }

        return Conference::all();
    }

    public function store(ConferenceRequest $request)
    {
        $data = $request->validated();
        $country = request()->country;
        $conference = Conference::create($data);
        Country::associateCountry($conference, $country);
        User::associateUser($conference);
        User::givePermissions();
        return $conference->load('country', 'reports', 'category');
    }

    public function show(Conference $conference)
    {
        return $conference->load('country', 'reports', 'category');
    }

    public function update(Conference $conference, ConferenceRequest $request)
    {
        $data = $request->validated();
        $country = $request->country_id;
        $conference->update($data);
        Country::associateCountry($conference, $country);
        return $conference->load('country', 'reports', 'category');
    }

    public function destroy(Conference $conference)
    {
        $conference->delete();
    }

    public function export(Request $request)
    {
        $conferences = $this->getFilteredConferences($request)->get();
        ProcessConferencesExport::dispatch($conferences)->delay(now()->addSeconds(5));
    }

    public function exportListeners(Conference $conference)
    {
        ProcessConferenceListenersExport::dispatch($conference)->delay(now()->addSeconds(5));
    }
}
