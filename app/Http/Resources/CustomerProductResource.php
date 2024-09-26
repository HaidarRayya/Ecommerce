<?php

namespace App\Http\Resources;

use App\Models\Favorite;

use App\Models\UserEvaluation;
use App\Models\ProductEvaluation;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class CustomerProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */

    public function toArray($request)
    {
        $favorites = Favorite::where("user_id", '=', Auth::user()->id)->get();
        $fav = 0;
        $favoriteId = 0;
        foreach ($favorites as $favorite) {
            if ($favorite->product_id == $this->id) {
                $favoriteId = $favorite->id;
                $fav = 1;
                break;
            }
        }
        $userEvaluations = UserEvaluation::where('seller_id', '=', $this->seller_id)->get();
        $productEvaluations = ProductEvaluation::where('product_id', '=', $this->id)->get();
        $user = User::where('id', '=',  $this->seller_id)->first();
        return [
            'favoriteId' => $favoriteId,
            'id' => $this->id,
            'name' => $this->name,
            'categoryName'  => $this->category_name,
            'categoryType' => $this->category_type,
            'price' => $this->price,
            'count' => $this->count,
            'image' => asset('storage/' .  $this->image),
            'description' => $this->description,
            'productEvaluation' => $this->evaluation,
            'userEvaluation' => $user->evaluation,
            'sellerId' => $this->seller_id,
            'sellerName' => $this->seller_name,
            'isFavorite' =>    $fav,
            'numbersUserEevaluations'  => count($userEvaluations),
            'numbersproductEevaluations'  => count($productEvaluations),

        ];
    }
}
