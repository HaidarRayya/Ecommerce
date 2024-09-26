<?php

namespace App\Http\Resources;

use App\Models\UserEvaluation;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if ($this->role == 'seller') {
            $userEvaluations = UserEvaluation::where('seller_id', '=', $this->id)->get();
            return [
                'id' => $this->id,
                'firstName' => $this->firstName,
                'lastName' => $this->lastName,
                'email'  => $this->email,
                'address'  => $this->address,
                'role'  => $this->role,
                'phoneNumber'  => $this->phoneNumber,
                'userEvaluation' => $this->evaluation,
                'numbersUserEevaluations'  => count($userEvaluations),
                'points'  => $this->points,
                'image' => $this->image == null ? "" : asset('storage/' .  $this->image),
            ];
        } else {
            return [
                'id' => $this->id,
                'firstName' => $this->firstName,
                'lastName' => $this->lastName,
                'email'  => $this->email,
                'address'  => $this->address,
                'role'  => $this->role,
                'phoneNumber'  => $this->phoneNumber,
                'points'  => $this->points,
                'image' => $this->image == null ? "" : asset('storage/' .  $this->image),

            ];
        }
    }
}