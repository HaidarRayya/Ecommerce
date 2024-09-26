<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\UserEvaluation;
use App\Models\ProductEvaluation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SellerProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $userEvaluations = UserEvaluation::where('seller_id', '=', $this->seller_id)->get();
        $productEvaluations = ProductEvaluation::where('product_id', '=', $this->id)->get();
        $user = User::where('id', '=', Auth::user()->id)->first();
        return [
            'id' => $this->id,
            'sellerId' => $this->seller_id,
            'name' => $this->name,
            'categoryName'  => $this->category_name,
            'categoryType' =>  $this->category_type,
            'price' => $this->price,
            'count' => $this->count,
            'image' => asset('storage/' .  $this->image),
            'description' => $this->description,
            'productEvaluation' => $this->evaluation,
            'userEvaluation' => $user->evaluation,
            'numbersUserEevaluations'  => count($userEvaluations),
            'numbersproductEevaluations'  => count($productEvaluations),
        ];
    }
}
