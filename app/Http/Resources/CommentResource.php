<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $user = User::where('id', '=', $this->user_id)->first();
        $isUpdated = Carbon::create($this->created_at)->notEqualTo(Carbon::create($this->updated_at));

        $date = Carbon::create($this->created_at);
        $now = now();
        $days = $date->diffInMinutes($now);

        return [
            'id'  => $this->id,
            'user_name' => $user->firstName . " " . $user->lastName,
            'user_id' => $user->id,
            'user_image' => $this->user == null ? "" : asset('storage/' .  $user->image),
            'description' => $this->description,
            'date' =>  self::func($days),
            'isUpdated' => $isUpdated
        ];
    }

    private function  func($minutes)
    {
        $x = 0;
        if ($minutes < 60 && $minutes > 0) {
            $x =  $minutes  .  " د";
        } else if ($minutes >= 60 && $minutes < 1440) {
            $y = intval($minutes / 60);
            $x =  $y  .  " س";
        } else if ($minutes > 1440 && $minutes < 43200) {
            $y = intval($minutes / 1440);
            $x =  $y  .  " ي";
        } else if ($minutes > 43200 && $minutes < 518400) {
            $y = intval($minutes / 43200);
            $x = $y  .  " ش";
        } else if ($minutes > 518400) {
            $y = intval($minutes / 518400);
            $x = $y  .  " سنة";
        } else {
            $x = "الأن";
        }
        return $x;
    }
}