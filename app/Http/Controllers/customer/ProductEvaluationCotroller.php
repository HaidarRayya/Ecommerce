<?php

namespace App\Http\Controllers\customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductEvaluation;
use App\Models\PurchaseHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class ProductEvaluationCotroller extends Controller
{

    public function checkProductEvaluation(Request $request)
    {
        $purchaseHistories = PurchaseHistory::where('user_id', '=', Auth::user()->id)
            ->where('product_id', '=', $request->product_id)->get();
        if ($purchaseHistories->isEmpty()) {
            throw ValidationException::withMessages([
                'message' => "لا يمكنك تقييم هذا المنتج لم تقم بشراءه بعد",
            ]);
        }
        return response(200);
    }
    public function evaluation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'evaluation' => 'required',
            'product_id' => 'required'
        ], [
            'evaluation.required' => 'يجب ادخال التقييم',
            'product_id.required' =>  'يجب ادخال رقم المنتج',
        ]);
        if ($validator->fails()) {
            throw ValidationException::withMessages([
                'message' => "البيانات المدخلة خاطئة",
            ]);
        }
        ProductEvaluation::create([
            'evaluation' => $request->evaluation,
            'customer_id' => Auth::user()->id,
            'product_id' => $request->product_id,
        ]);
        self::updateSellerEvalution($request->product_id);
        return  response()->json([
            'message' => "تم تقييم بنجاح"
        ]);
    }
    private function updateSellerEvalution($product_id)
    {
        $evaluations = ProductEvaluation::where('product_id', '=', $product_id)->get();
        $x = 0;
        $i = 0;
        foreach ($evaluations as $evaluation) {
            $x += $evaluation->evaluation;
            $i++;
        }
        $evaluation = $x / $i;
        $product = Product::where('id', '=', $product_id)->first();
        $product->update([
            'evaluation' => $evaluation
        ]);
    }
}