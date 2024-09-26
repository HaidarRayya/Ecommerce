<?php

namespace App\Http\Resources;

use App\Models\UserEvaluation;
use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use App\Models\ProductEvaluation;
use App\Models\User;

class CartProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $product = Product::where('id', '=', $this->product_id)->first();
        $favorites = Favorite::where("user_id", '=', Auth::user()->id)->get();
        $fav = 0;
        foreach ($favorites as $favorite) {
            if ($favorite->product_id == $this->id) {
                $fav = 1;
                break;
            }
        }
        $userEvaluations = UserEvaluation::where('seller_id', '=', $this->seller_id)->get();
        $productEvaluations = ProductEvaluation::where('product_id', '=', $this->id)->get();
        $user = User::where('id', '=', $product->seller_id)->first();
        return [
            'cartId' => $this->id,
            'price' => $this->price,
            'categoryName'  => $product->category_name,
            'categoryType' => $product->category_type,
            'quantity' => $this->quantity,
            'image' => asset('storage/' .  $product->image),
            'sellerId' => $product->seller_id,
            'sellerName' => $product->seller_name,
            'id' => $product->id,
            'name' => $product->name,
            'count' => $product->count,
            'description' => $product->description,
            'isFavorite' =>    $fav,
            'productEvaluation' => $product->evaluation,
            'userEvaluation' => $user->evaluation,
            'numbersUserEevaluations'  => count($userEvaluations),
            'numbersproductEevaluations'  => count($productEvaluations),
        ];
    }
}