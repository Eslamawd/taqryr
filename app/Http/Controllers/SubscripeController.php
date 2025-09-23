<?php

namespace App\Http\Controllers;

use App\Models\PlanSubscripe;
use App\Models\Subscripe;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
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

    $totalInCents = (int) round($price * 100); // السعر بالسنت
    $balance = $user->balanceInt; // الرصيد بالسنت

    if ($balance < $totalInCents) {
        return response()->json([
            'message' => 'Your wallet balance is insufficient.',
        ], 422);
    }

    // خصم من المحفظة
   

    $subscription = $user->subscripe;

    if ($subscription) {
        // لو الاشتراك لسه شغال نزود على نهايته
        if ($subscription->end_at > now()) {
            $startsAt = $subscription->end_at;
        } else {
            // لو منتهي نبدأ من دلوقتي
            $startsAt = now();
        }

        $endsAt = $startsAt->copy()->addDays($durationDays);

        // تحديث نفس الاشتراك
        $subscription->updateOrCreate([
            'plan' => $plan->name,
            'price'   => $price,
            'total'   => $price,
            'start_date' => $startsAt,
            'end_date' => $endsAt,
        ]);
         $user->withdraw($totalInCents);
    } else {
        // لو مفيش اشتراك جديد
        $startsAt = now();
        $endsAt = $startsAt->copy()->addDays($durationDays);

        $subscription = Subscripe::create([
            'user_id' => $user->id,
            'plan' => $plan->name,
            'price'   => $price,
            'total'   => $price,
            'start_date' => $startsAt,
            'end_date' => $endsAt,
        ]);
         $user->withdraw($totalInCents);
    }
    return response()->json([
        'message'      => 'Subscription Renewed successfully',
        'subscription' => $subscription,
    ], 201);
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
