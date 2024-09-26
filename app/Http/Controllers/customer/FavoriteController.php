<?php

namespace App\Http\Controllers\customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\FavoriteProductResource;
use Illuminate\Http\Request;
use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $favoriteProducts = Favorite::where('user_id', '=', Auth::user()->id)->get();

        $favoriteProducts = FavoriteProductResource::collection($favoriteProducts);
        return  response()->json([
            'favoriteItmes' => $favoriteProducts
        ]);
    }


    public function addFavorite($product_id)
    {

        $product = Product::where("id", '=', $product_id)->first();
        $x = Favorite::where('user_id', '=', Auth::user()->id)->where('product_id', '=', $product_id)->first();
        if ($x != null) {
            return;
        }
        $favorite = Favorite::create([
            'product_id' => $product->id,
            'user_id' => Auth::user()->id
        ]);
        return  response()->json([
            'message' => "تمت اضافة العنصر الى المفضلة بنجاح",
            'favoriteId' => $favorite->id
        ]);
    }
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
    public function destroy(Favorite $favorite)
    {
        $favorite->delete();
        return  response()->json([
            'message' => "ـم حذف منتج بنجاح"
        ]);
    }
}
