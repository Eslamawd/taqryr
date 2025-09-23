<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\MetaCreateAdsJob;
use App\Jobs\SnapchatCreateAds;
use App\Models\Ad;
use Illuminate\Http\Request;

class AdsCreateController extends Controller
{
    //
       public function index()
    {
        $ads = Ad::with(['target', 'creative'])->paginate(6);
        return response()->json([
        'ads' => $ads,
    ]);

    }
    public function store (Request $request, $id) {
        $ad = Ad::findOrFail($id);
        if ($ad->platform === 'snap') {
            SnapchatCreateAds::dispatch($ad);
        } else if ($ad->platform === 'meta') {

        MetaCreateAdsJob::dispatch($ad);
        }
        

        return response()->json(['message'=>'Created New Ads Sucsess']);
    }
}
