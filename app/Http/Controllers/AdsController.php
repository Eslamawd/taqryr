<?php

namespace App\Http\Controllers;

use App\Mail\SendPriceAdsMail;
use App\Models\Ad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AdsController extends Controller
{
    //
     public function store(Request $request)
    {


           if ($request->has('targets') && is_string($request->input('targets'))) {
        $decoded = json_decode($request->input('targets'), true);
        $request->merge(['targets' => $decoded]);
    }
        // التحقق من المدخلات
        $validated = $request->validate([
            
            'platform'        => 'required|array|min:1',
            'platform.*'      => 'in:snap,meta,google,tiktok',
            'name'           => 'required|string|max:255',
            'name_brand'           => 'required|string|max:255',
            'headline'           => 'required|string|max:255',
            'objective'      => 'nullable|string|max:100',
            'budget'         => 'required|numeric|min:1',
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',     
            'files'              => 'required|array|min:1',
            'files.*'            => 'file|mimes:jpg,jpeg,png,mp4,mov,webm,avi|max:51200', // 50MB
            'targets'        => 'required|array|min:1',
            'targets.*.country'  => 'required|array|max:5',
            'targets.*.gender'   => 'required|in:male,female,all',
            'targets.*.age_group' => 'nullable|string',
            'targets.*.region'    => 'nullable|array',
            'targets.*.languages' => 'nullable|array',
            'targets.*.os_type'   => 'nullable|array',
            'targets.*.interests'=> 'nullable|array',
        ]);

        $user = auth()->user();
        $userId = auth()->id();
        $price = $validated['budget'];

        $platformsCount = count($validated['platform']);
        $totalBudget = $validated['budget'] * $platformsCount; // إجمالي الباجت
         $totalInCents = (int) round($totalBudget * 100);

        
    $balance = $user->balanceInt; // الرصيد بالسنت

    if ($balance < $totalInCents) {
        return response()->json([
            'message' => 'Your wallet balance is insufficient.',
        ], 422);
    }

    $uploadedFiles = [];

     foreach ($validated['files'] as $file) {
                $path = $file->store('ads', 'public');
                $type = str_starts_with($file->getMimeType(), 'video') ? 'VIDEO' : 'IMAGE';

                 $uploadedFiles[] = [
                'path' => $path,
                'type' => $type,
            ];
            }
        // wrap inside transaction
        DB::beginTransaction();
        

        try {
            // إنشاء الإعلان

            
        $ads = [];

        foreach ($validated['platform'] as $platform) {
            $ad = Ad::create([
                'user_id'       => $userId,
                'platform'      => $platform,
                'name'          => $validated['name'],
                'objective'     => $validated['objective'] ?? null,
                'budget'        => $validated['budget'],
                'status'        => 'pending',
                'start_date'    => $validated['start_date'] ?? null,
                'end_date'      => $validated['end_date'] ?? null,
            ]);

            $ad->brand()->create([
                'name_brand' => $validated['name_brand'],
                'headline' =>$validated['headline'],

            ]);

             foreach ($uploadedFiles as $file) {
           
                 $ad->creative()->create([
                    'file_path'  => $file['path'],
                    'platform'   => $platform,
                    'type'       => $file['type'],
                ]);

               
            }
            // حفظ الاستهدافات
            foreach ($validated['targets'] as $target) {
                            if (isset($target['age_group'])) {
                                    [$ageMin, $ageMax] = explode('-', $target['age_group']);
                                    $target['age_min'] = (int) $ageMin;
                                    $target['age_max'] = (int) $ageMax;
                                }
                $ad->target()->create([
                    'ad_id'     => $ad->id,
                    'country'   => $target['country'][0],
                    'gender'    => $target['gender'],
                    'age_min'   => $target['age_min'],
                    'age_max'   => $target['age_max'],    
                    'options'   => json_encode([
                        'age_group' => $target['age_group'] ?? null,
                        'region'    => $target['region'] ?? [],
                        'languages' => $target['languages'] ?? [],
                        'os_type'   => $target['os_type'] ?? [],
                    ]),
                   'interests' => json_encode($target['interests']) ?? null, 
                ]);

            }
            $ads[] = $ad;
        }

            DB::commit();
            
       
            // إرسال الميل بعد الخصم
            return response()->json([
                'status' => 'success',
                'ad' => $ads,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

public function index()
{
    $ads = auth()->user()
        ->ads()
        ->with(['creative', 'target']) // ❌ شيل stats
        ->latest()
        ->paginate(6);

    // نضيف today_stats لكل إعلان
    $ads->getCollection()->transform(function ($ad) {
       $ad->setAttribute('today_stats', $ad->today_stats);  // accessor
        return $ad;
    });

    return response()->json(['ads' => $ads]);
}


    
}
