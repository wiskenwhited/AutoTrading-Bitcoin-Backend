<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;

class CityController extends ApiController
{
    function index(Request $request)
    {
        $query = City::getQuery();
        $filters = $this->getFilterData($request);
        if ($country = array_get($filters, 'country')) {
            $query->where('country_code', trim($country));
        }
        if ($city = array_get($filters, 'city')) {
            $query->where('city_name', 'LIKE', ltrim($city) .'%');
        }
        $total = $query->count();
        $query = $this->applyPaginationData($request, $query, ['page' => ['limit' => null]]);
        $cities = $query->get();

        return response()->json([
            'data' => $cities,
            'meta' => $this->getResponseMetadata($request, $total)
        ]);
    }
}