<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\SnapchatTargetingOption;
use Illuminate\Http\Request;

class TargetingController extends Controller
{
    public function index(Request $request)
    {
        $query = SnapchatTargetingOption::query();

        // لو فيه فلتر حسب الدولة
        if ($request->has('country_code')) {
            $countryCode = strtolower(is_array($request->country_code) ? $request->country_code[0] : $request->country_code);


            $query->where('country_code', $countryCode);
        }

        // لو فيه فلتر حسب النوع
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $targeting = $query->get()->all();

        return response()->json([
            'status' => true,
            'data' => $targeting
        ]);
    }

    public function getCountry() {
        $country =  Country::all();
        return response()->json(["country"=> $country]);
    }
}
