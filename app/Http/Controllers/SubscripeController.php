<?php

namespace App\Http\Controllers;

use App\Mail\SendSubMail;
use App\Models\PlanSubscripe;
use App\Models\Subscripe;
use Carbon\Carbon;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class SubscripeController extends Controller
{
    //

    public function index () {
        $subscriptions = Subscripe::latest()->paginate(10);
        return response()->json(['subscriptions' => $subscriptions]);
    }

public function store(Request $request, $id)
{
    $user = auth()->user();
    $plan = PlanSubscripe::findOrFail($id);

    $price = $plan->price;
    $durationDays = $plan->duration_days;

    $totalInCents = (int) round($price * 100); 
    $balance = $user->balanceInt; 

    if ($balance < $totalInCents) {
        return response()->json([
            'message' => 'Your wallet balance is insufficient.',
        ], 422);
    }

    DB::beginTransaction();

    try {
        $subscription = $user->subscripe;

        if ($subscription) {
            // لو الاشتراك لسه شغال نزود على نهايته
            $startsAt = $subscription->end_date > now()
                ? Carbon::parse($subscription->end_date)
                : now();

            $endsAt = $startsAt->copy()->addDays($durationDays);

            $subscription->update([
                'plan'       => $plan->name,
                'price'      => $price,
                'end_date'   => $endsAt,
            ]);
        } else {
            $startsAt = now();
            $endsAt = $startsAt->copy()->addDays($durationDays);

            $subscription = Subscripe::create([
                'user_id'    => $user->id,
                'plan'       => $plan->name,
                'price'      => $price,
                'start_date' => $startsAt,
                'end_date'   => $endsAt,
            ]);
        }

        // خصم الرصيد
        $user->withdraw($totalInCents);

        DB::commit();

        // إرسال الميل بعد نجاح العملية
        Mail::to($user->email)->send(new SendSubMail($subscription));

        return response()->json([
            'message'      => 'Subscription renewed successfully',
            'subscription' => $subscription,
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Something went wrong: ' . $e->getMessage(),
        ], 500);
    }
}

public function count(){
    $count = Subscripe::count();
    return response()->json(['count' => $count]);
}

public function getRevenue() {
    $revenue = Subscripe::sum('price');
    return response()->json(['revenue' => $revenue]);
}

}
