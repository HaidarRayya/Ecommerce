<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class OperationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $user = User::find($this->user_id);
        return [
            'id'  => $this->id,
            'user_name' => $user->firstName . " " . $user->lastName,
            'email' => $user->email,
            'type' => $this->type,
            'date' => $this->date,
            'amount' => $this->amount,
        ];
    }
}