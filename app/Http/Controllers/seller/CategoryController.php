<?php

namespace App\Http\Controllers\seller;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\RequestCategory;
use App\Models\SellerCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getAllCategory()
    {
        $categories = Category::all();

        return response()->json([
            'allCategories' => $categories,
        ]);
    }
    public function getMyCategory()
    {
        $categories = SellerCategory::where('seller_id', '=', Auth::user()->id)->get();
        $categories = CategoryResource::collection($categories);
        return response()->json([
            'myCategories' => $categories,
        ]);
    }

    public function getMyRequestCategory()
    {
        $requestCategories = RequestCategory::where('seller_id', '=', Auth::user()->id)->get();
        return response()->json([
            'requestCategories' => $requestCategories,
        ]);
    }

    public function addCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'type' => 'required'
        ], [
            'name.required' => 'يجب اضافة اسم النوع',
            'name.min' => 'يجب ان يكون اسم النوع اكبر من ثلاث محارف',
            'type' => ' يجب اضافة اذا كان النوع مستعمل او جديد',
        ]);
        if ($validator->fails()) {
            throw ValidationException::withMessages([
                'message' => "البيانات المدخلة خاطئة",
                'errors' => $validator->errors()
            ]);
        }
        $category = Category::where('name', '=', $request->name)->where('type', '=', $request->type)->first();

        $requestCategory = RequestCategory::where('seller_id', '=', Auth::user()->id)
            ->where('type', '=', $request->type)->where('name', '=', $request->name)->where('type', '=', $request->type)->first();
        if ($requestCategory != null) {
            return response()->json([
                'message' =>  'تمت اضافة هذا الطلب مسبقا '
            ]);
        }
        if ($category) {
            SellerCategory::create([
                'category_id' => $category->id,
                'seller_id' => Auth::user()->id
            ]);
            return response()->json([
                'message' => 'تمت اضافة النوع بنجاح',
            ]);
        } else {
            RequestCategory::create([
                'name' => $request->name,
                'type' => $request->type,
                'seller_id' => Auth::user()->id
            ]);
            return response()->json([
                'message' => 'سوف تتم مراجعة النوع الذي تريده وارسال اليك اشعار بحالة الطلب',
            ]);
        }
    }
    public function getAllProductCategory($category_id)
    {
        $produtcs = Product::where('seller_id', '=', Auth::user()->id)->where('category_id', '=', $category_id)->get();

        return response()->json([
            'produtcs' => $produtcs,
        ]);
    }
    public function destroyCategory($sellerCategoryId)
    {
        $sellerCategory = SellerCategory::where('id', '=', $sellerCategoryId)->first();

        $products = Product::where('seller_id', '=', Auth::user()->id)
            ->where('category_id', '=', $sellerCategory->category_id)->get();
        foreach ($products as $p) {
            ProductController::destroy($p);
        }
        $sellerCategory->delete();
        return response()->json([
            'message' => 'تم حذف النوع و جميع منتجات بنجاح',
        ]);
    }

    public function destroyRequestCategory($requestCategoryId)
    {
        $requestCategory = RequestCategory::find($requestCategoryId)->first();

        $requestCategory->delete();
        return response()->json([
            'message' => 'تم حذف طلبك بنجاح',
        ]);
    }
}
