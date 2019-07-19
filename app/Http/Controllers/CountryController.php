<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends ApiController
{
    function index(Request $request)
    {
        $query = Country::getQuery();
        $total = $query->count();
        $query = $this->applyPaginationData($request, $query, ['page' => ['limit' => null]]);
        $countries = $query->get();

        return response()->json([
            'data' => $countries,
            'meta' => $this->getResponseMetadata($request, $total)
        ]);
    }
}