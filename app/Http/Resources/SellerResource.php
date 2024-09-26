<?php

namespace App\Http\Resources;

use App\Models\UserEvaluation;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $userEvaluations = UserEvaluation::where('seller_id', '=', $this->id)->get();
        return [
            'id' => $this->id,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'address'  => $this->address,
            'phoneNumber'  => $this->phoneNumber,
            'userEvaluation' => $this->evaluation,
            'numbersUserEevaluations'  => count($userEvaluations),
            'image' => $this->image == null ? "" : asset('storage/' .  $this->image),
        ];
    }
}