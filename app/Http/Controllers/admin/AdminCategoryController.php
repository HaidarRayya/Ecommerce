<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\NotificationController;
use App\Models\Category;
use App\Models\RequestCategory;
use App\Models\SellerCategory;
use Illuminate\Http\Request;

class AdminCategoryController extends Controller
{

    public function getAllCategory()
    {
        $categories = Category::all();

        return response()->json([
            'allCategories' => $categories,
        ]);
    }
    public function getRequestCategory()
    {
        $categories = RequestCategory::all();

        return response()->json([
            'requestCategory' => $categories,
        ]);
    }
    public function accept($id, Request $request)
    {
        $requestCategory = RequestCategory::where('id', '=', $id)->first();
        $requestCategories1 = RequestCategory::where('name', '=', $requestCategory->name)->where('type', '=', $requestCategory->type)->get();
        $requestCategories2 = RequestCategory::where('name', '=', $requestCategory->name)->where('type', '!=', $requestCategory->type)->get();

        $category1 = Category::where('name', '=', $requestCategory->name)->where('type', '=', $requestCategory->type)->first();
        $category2 = 0;
        if ($category1 == null) {
            $category1 =  Category::create([
                'name' => $requestCategory->name,
                'type' => $requestCategory->type
            ]);
            $category2 = Category::create([
                'name' => $requestCategory->name,
                'type' => $requestCategory->type == 1 ? 2 : 1
            ]);
        }
        $category2 = Category::where('name', '=', $requestCategory->name)->where('type', '!=', $requestCategory->type)->first();
        foreach ($requestCategories1 as $x) {
            SellerCategory::create([
                'seller_id' => $x->seller_id,
                'category_id' => $category1->id
            ]);
            $notification = [
                'user_id' => $x->seller_id,
                'descripation' => $request->descripation,
                'date' => now(),
                'redirection' => $request->redirection
            ];
            NotificationController::store($notification);
            $x->delete();
        }
        foreach ($requestCategories2 as $x) {
            SellerCategory::create([
                'seller_id' => $x->seller_id,
                'category_id' => $category2->id
            ]);
            $notification = [
                'user_id' => $x->seller_id,
                'descripation' => $request->descripation,
                'date' => now(),
                'redirection' => $request->redirection
            ];
            NotificationController::store($notification);
            $x->delete();
        }
        return response()->json([
            'message' => 'تمت قبول جميع طلبات هذا النوع',
        ]);
    }
    public function reject($id, Request $request)
    {
        $requestCategory = RequestCategory::where('id', '=', $id)->first();
        $requestCategories = RequestCategory::where('name', '=', $requestCategory->name)->get();
        foreach ($requestCategories as $x) {
            $notification = [
                'user_id' => $x->seller_id,
                'descripation' => $request->descripation,
                'date' => now(),
                'redirection' => $request->redirection
            ];
            NotificationController::store($notification);
            $x->delete();
        }
        return response()->json([
            'message' => 'ـم رفض جميع طلبات هذا النوع'
        ]);
    }
}
