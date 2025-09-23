<?php
namespace App\Http\Controllers;

use App\Jobs\SnapchatSyncJob;

class PlatformCatalogController extends Controller
{
    public function sync( )
    {
        SnapchatSyncJob::dispatch();
        
        return response()->json(['status'=>'success']);
    }

  
}
