<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Country;
use Illuminate\Http\Request;

/**
 * Class PublicController
 * @package App\Http\Controllers
 * @deprecated Deprecated in favour of specific controllers
 */
class PublicController extends Controller
{
    function country(Request $request)
    {
        $countries = Country::GetDropdown();

        return response()->json(['countries' => $countries]);
    }

    function city(Request $request)
    {
        $cities = [];
        $limit = 50;

        if ($request->query('limit')) {
            $limit = intval($request->query('limit'));
        }

        if ($request->query('country')) {
            $cityName = ltrim($request->query('city'));
            $cities = City::select("city_name as text", "id")->where("country_code", $request->query('country'))->where("city_name", "LIKE", $cityName . "%")->limit($limit)->get();
            if ($cities) {
                $cities = $cities->toArray();
            }
        }

        return response()->json(['cities' => $cities]);
    }
}