<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\University;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function cities(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:3',
        ]);

        $cities = City::where('name', 'ilike', '%' . $request->q . '%')
            ->limit(10)
            ->get(['id', 'name']);

        return response()->json($cities);
    }

    public function universities(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:3',
        ]);

        $universities = University::where('name', 'ilike', '%' . $request->q . '%')
            ->limit(10)
            ->get(['id', 'name']);

        return response()->json($universities);
    }
}
