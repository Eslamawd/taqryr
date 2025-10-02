<?php

namespace App\Http\Controllers;

use App\Models\PlanSubscripe;
use Illuminate\Http\Request;

class PlanSubscripeController extends Controller
{
    //
    public function index()
    {
        $plans = PlanSubscripe::all();
        return response()->json($plans);
    }

    public function show($id)
    {
        $plan = PlanSubscripe::find($id);
        if (!$plan) {
            return response()->json(['message' => 'الخطة غير موجودة'], 404);
        }
        return response()->json($plan);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'features' => 'nullable|array',
            'features.*.title' => 'string|max:255',
            'features.*.title_ar' => 'string|max:255',

        ]);

        $plan = PlanSubscripe::create($validated);
        return response()->json($plan, 201);
    }
    public function update(Request $request, $id)
    {
        $plan = PlanSubscripe::find($id);
        if (!$plan) {
            return response()->json(['message' => 'الخطة غير موجودة'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'duration_days' => 'sometimes|required|integer|min:1',
            'features' => 'nullable|array',
            'features.*.title' => 'string|max:255',
            'features.*.title_ar' => 'string|max:255',

        ]);

        $plan->update($validated);
        return response()->json($plan);
    }
    public function destroy($id)
    {
        $plan = PlanSubscripe::find($id);
        if (!$plan) {
            return response()->json(['message' => 'الخطة غير موجودة'], 404);
        }

        $plan->delete();
        return response()->json(['message' => 'تم حذف الخطة بنجاح']);
    }
}
