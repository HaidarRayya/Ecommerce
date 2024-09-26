<?php

namespace App\Http\Resources;

use App\Models\UserEvaluation;
use App\Models\Product;
use App\Models\ProductEvaluation;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteProductResource extends JsonResource
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

        $userEvaluations = UserEvaluation::where('seller_id', '=', $this->seller_id)->get();
        $productEvaluations = ProductEvaluation::where('product_id', '=', $this->id)->get();
        $user = User::where('id', '=', $product->seller_id)->first();
        return [
            'favoriteId' => $this->id,
            'id' => $product->id,
            'name' => $product->name,
            'categoryName'  => $product->category_name,
            'categoryType' => $product->category_type,
            'price' => $product->price,
            'count' => $product->count,
            'image' => asset('storage/' .  $product->image),
            'description' => $product->description,
            'sellerId' => $product->seller_id,
            'sellerName' => $product->seller_name,
            'isFavorite' =>   1,
            'productEvaluation' => $product->evaluation,
            'userEvaluation' => $user->evaluation,
            'numbersUserEevaluations'  => count($userEvaluations),
            'numbersproductEevaluations'  => count($productEvaluations),
        ];
    }
}