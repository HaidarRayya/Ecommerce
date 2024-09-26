<?php

namespace App\Http\Controllers\customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseHistory;
use App\Models\UserEvaluation;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserEvaluationController extends Controller
{
    public function checkUserEvaluation(Request $request)
    {
        $purchaseHistories = PurchaseHistory::where('user_id', '=', Auth::user()->id)->orderByDesc('created_at')->get();

        foreach ($purchaseHistories as $purchaseHistory) {
            $product = Product::where('id', '=', $purchaseHistory->product_id)->first();
            if ($product->seller_id == $request->seller_id) {
                return response(200);
            }
        }
        throw ValidationException::withMessages([
            'message' => "لا يمكنك تقييم هذا البائع لم تقم بشراء منتجات من عنده بعد",
        ]);
    }
    public function evaluation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'evaluation' => 'required',
            'seller_id' => 'required'
        ], [
            'evaluation.required' => 'يجب ادخال التقييم',
            'seller_id.required' =>  'يجب ادخال رقم البائع',
        ]);
        if ($validator->fails()) {
            throw ValidationException::withMessages([
                'message' => "البيانات المدخلة خاطئة",
            ]);
        }
        UserEvaluation::create([
            'evaluation' => $request->evaluation,
            'customer_id' => Auth::user()->id,
            'seller_id' => $request->seller_id,
        ]);
        self::updateSellerEvalution($request->seller_id);
        return  response()->json([
            'message' => "تم تقييم بنجاح"
        ]);
    }
    private function updateSellerEvalution($seller_id)
    {
        $evaluations = UserEvaluation::where('seller_id', '=', $seller_id)->get();
        $x = 0;
        $i = 0;
        foreach ($evaluations as $evaluation) {
            $x += $evaluation->evaluation;
            $i++;
        }
        $evaluation = $x / $i;
        $user = User::where('id', '=', $seller_id)->first();
        $user->update([
            'evaluation' => $evaluation
        ]);
    }
}
