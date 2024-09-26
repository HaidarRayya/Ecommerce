<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        return [
            'id' => $this->id,
            'userId' => $this->user_id,
            'redirection' => $this->redirection,
            'descripation'  => $this->descripation,
            'sellerId'  => $this->seller_id,
            'date' => $this->date,
            'data' => json_decode($this->data, JSON_UNESCAPED_SLASHES),
            'reading' => $this->reading,
        ];
    }
}
