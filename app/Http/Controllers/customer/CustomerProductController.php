<?php

namespace App\Http\Controllers\customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Resources\CustomerProductResource;
use App\Http\Resources\SellerResource;
use App\Models\User;

class CustomerProductController extends Controller
{
    /** price=&name=&category_type=&category_name=&
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $product_name = $request->input("product_name");
        $seller_name = $request->input("seller_name");
        $category_name = $request->input("category_name");
        $price = $request->input("price");
        $category_type = $request->input("category_type");

        $products = Product::query();
        $products->where('count', '>', '0')->where('deleted_at', '=', null);
        $products->when($product_name, function ($query) use ($product_name) {
            $query->where('name', 'LIKE', '%' . $product_name . '%');
        })->when($seller_name, function ($query) use ($seller_name) {
            $query->where('seller_name', 'LIKE', '%' . $seller_name . '%');
        })->when($category_name, function ($query) use ($category_name) {
            $query->where('category_name', 'LIKE', '%' . $category_name . '%');
        })->when($price, function ($query) use ($price) {
            $query->where('price', '<=', $price);
        })->when($category_type, function ($query) use ($category_type) {
            $query->where('category_type', '=', $category_type);
        });

        $products = $products->get();
        $products = CustomerProductResource::collection($products);
        return response()->json([
            'products' => $products
        ]);
    }

    public function getAllSeller(Request $request)
    {
        $seller_name = $request->input("seller_name");

        $sellers = User::query();

        $sellers = $sellers->where('role', '=', 'seller');
        $sellers = $sellers->when($seller_name, function ($query) use ($seller_name) {
            $query->where('firstName', 'LIKE', '%' . $seller_name . '%')
                ->orWhere('lastName', 'LIKE', '%' . $seller_name . '%');
        });
        $sellers = $sellers->get();

        $sellers = SellerResource::collection($sellers);
        return response()->json([
            'sellers' => $sellers
        ]);
    }



    public function getAllproducts($seller_id)
    {
        $products = Product::where('seller_id', '=', $seller_id)->get();
        $products = CustomerProductResource::collection($products);
        return response()->json([
            'products' => $products
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}