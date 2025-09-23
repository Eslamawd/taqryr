<?php
// app/Http/Controllers/PaymentController.php

namespace App\Http\Controllers;

use App\Models\PaymentReq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{

    /**
     * عرض جميع طلبات الدفع (للمسؤول فقط)
     */
    public function index()
    {
        $payments = PaymentReq::with('user')->orderBy('created_at', 'desc')->paginate(10);
        return response()->json(['payments' => $payments]);
    }

    /**
     * عرض طلبات الدفع الخاصة بالمستخدم الحالي
     */
    public function getByUser(Request $request)
    {
        $user = $request->user();
        $payments = $user->payments()->orderBy('created_at', 'desc')->paginate(10);
        return response()->json(['payments' => $payments]);
    }

        /**
        * تحديث حالة طلب الدفع (للمسؤول فقط)
        */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $payment = PaymentReq::findOrFail($id);
        $payment->update(['status' => $request->status]);

        return response()->json(['message' => 'Payment status updated successfully']);
    }

    /**
     * استلام طلبات الدفع عبر التحويل البنكي
     */

    public function banking(Request $request)
    {
        // من المفترض أنك بتستقبل بيانات الدفع من الـ frontend
       $validated =  $request->validate([
            'amount' => 'required|numeric|min:1',
            'image'  => 'required|image|mimes:jpg,jpeg,png,webp|max:2048', 
        ]);

        $user = $request->user();

         if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('banking', 'public'); // غيّرت المجلد إلى 'products'
        $validated['image'] = $imagePath;
    }
        $validated['user_id'] = $user->id;
        $paymentReq = PaymentReq::create($validated);
     
            return response()->json([
                'message' => 'تم شحن المحفظة بنجاح',
                'payment' => $paymentReq,
            ]);
       
    }

    /**
     * إنشاء فاتورة دفع جديدة عبر Moyasar
     */

  public function create(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $user = $request->user();

        try {
            $response = Http::withBasicAuth(env('MOYASAR_SECRET_KEY'), '')
                ->post('https://api.moyasar.com/v1/invoices', [
                    'amount'       => $request->amount * 100, // هلّل (halalas) مش ريالات
                    'currency'     => 'SAR',
                    'description'  => 'شحن المحفظة للمستخدم #' . $user->id,
                    'callback_url' => 'https://34cd127a4d8b.ngrok-free.app/payment/callback' , // backend callback
                    'return_url'   =>  'https://bf20cd7803b7.ngrok-free.app /wallet', // العميل يرجع عليها
                    'metadata'     => [
                        'user_id' => $user->id,
                    ]
                ]);

            if ($response->failed()) {
                return response()->json([
                    'message' => 'فشل إنشاء الفاتورة',
                    'status'  => 400,
                    'body'    => $response->json(),
                ], 400);
            }

            return response()->json([
                'invoice_id' => $response['id'],
                'status'     => $response['status'],
                'amount'     => $response['amount'],
                'url'        => $response['url'], // رابط الدفع (ترسله للـ frontend)
            ]);
        } catch (\Throwable $e) {
            Log::error('خطأ في إنشاء الفاتورة: ' . $e->getMessage());
            return response()->json([
                'message' => 'حصل خطأ غير متوقع',
            ], 500);
        }
    }

    /**
     * تأكيد الدفع وإضافة الرصيد للمحفظة
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|string',
        ]);

        $paymentId = $request->input('payment_id');

        // جلب بيانات الدفع من Moyasar للتأكد
        $response = Http::withBasicAuth(config('services.moyasar.secret_key'), '')
            ->get("https://api.moyasar.com/v1/payments/{$paymentId}");

        if ($response->failed()) {
            return response()->json(['message' => 'فشل الاتصال بموياسار'], 502);
        }

        $payment = $response->json();

        // التحقق من الدفع
        if (
            isset($payment['status']) &&
            $payment['status'] === 'paid' &&
            isset($payment['metadata']['user_id'])
        ) {
            $user = User::find($payment['metadata']['user_id']);
            if ($user) {
                $amount = $payment['amount']; // تحويل من halalas لـ SAR
                $user->deposit($amount); // باستخدام laravel-wallet

                return response()->json([
                    'message' => 'تم شحن المحفظة بنجاح',
                    'balance' => $user->balance,
                ]);
            }
        }

        return response()->json(['message' => 'لم يتم تأكيد الدفع أو بيانات غير صحيحة'], 400);
    }


     /**
     * يستقبل إشعار الدفع من Moyasar ويتحقق من حالة الفاتورة
     */
    public function callback(Request $request)
    {
        // Moyasar بيبعت invoice.id
        $invoiceId = $request->input('id') ?? $request->input('invoice_id');

        if (!$invoiceId) {
            return response()->json(['message' => 'لا يوجد معرف فاتورة'], 400);
        }


      Log::info('Moyasar callback payload', $request->all());
        // جلب تفاصيل الفاتورة من Moyasar للتأكد
        $response = Http::withBasicAuth(config('services.moyasar.secret_key'), '')
            ->get("https://api.moyasar.com/v1/invoices/{$invoiceId}");

        if ($response->failed()) {
            return response()->json(['message' => 'فشل الاتصال بموياسار'], 502);
        }

        $invoice = $response->json();

        // التأكد أن الفاتورة مدفوعة
        if (
            isset($invoice['status']) &&
            $invoice['status'] === 'paid' &&
            isset($invoice['metadata']['user_id'])
        ) {
            $user = User::find($invoice['metadata']['user_id']);

            if ($user) {
                $amount = $invoice['amount'] ; // من هللات إلى ريال
                // شحن المحفظة — على افتراض أنك بتستخدم laravel-wallet
                $user->deposit($amount);

                return response()->json([
                    'message' => 'تم شحن المحفظة بنجاح',
                    'balance' => $user->balance,
                ]);
            }
        }

        return response()->json(['message' => 'لم يتم تأكيد الدفع أو بيانات غير صحيحة'], 400);
    }
}
