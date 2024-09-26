<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Resources\Json\JsonResource;

class productOrderResourse extends JsonResource
{

    public function toArray($request)
    {
        $product = Product::where('id', '=', $this->product_id)
            ->withTrashed()->first();
        return [
            'id' => $product->id,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'productName' =>  $product->name,
            'image' => asset('storage/' .  $product->image),
            'categoryName'  => $product->category_name,
            'categoryType' =>  $product->category_type,
        ];
    }
}