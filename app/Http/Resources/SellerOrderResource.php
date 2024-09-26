<?php

namespace App\Http\Resources;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $customer = User::where('id', '=', $this->customer_id)->first();
        $delivery = User::where('id', '=', $this->deliveryDriver_id)->first();

        $date = now();
        $expectedArrivalDate = Carbon::create($this->expectedArrivalDate);
        $hours = $expectedArrivalDate->diffInHours($date);
        $sta = '';
        $arrDat = '';
        switch ($this->status) {
            case 1:
                $sta = "الطلبية قيد المراجعة";
                $arrDat = '';
                break;
            case 2:
                $sta = "تم قبول الطلبية";
                $arrDat =  self::func($hours, $expectedArrivalDate);
                break;
            case 3:
                $sta = "الطلبية قيد التوصيل";
                $arrDat =  self::func($hours, $expectedArrivalDate);

                break;
            case 4:
                $sta = "تم التوصيل";
                $arrDat = '';
                break;
        }

        return [
            'id' => $this->id,
            'expectedArrivalDate' => $arrDat,
            'date' => $this->created_at,
            'customerName' => $customer->firstName . " " . $customer->lastName,
            'customerAddress' => $customer->address,
            'customerPhoneNumber' => $customer->phoneNumber,
            'deliveryName' => $delivery == null ? "" : $delivery->firstName . " " . $delivery->lastName,
            'deliveryphoneNumber' => $delivery == null ? "" : $delivery->phoneNumber,
            'status' =>  $sta,
            'statusCode' => $this->status,
            'totalPrice' => $this->totalprice,
            'image' => $customer->image == null ? "" : asset('storage/' .  $customer->image),
        ];
    }
    private function  func($hours, $expectedArrivalDate)
    {
        $x = 0;
        $date =  Carbon::create(now());
        if ($hours < 24 && $hours >= 0) {
            if ($expectedArrivalDate->day == $date->day) {
                $x = "اليوم";
            } else {
                $x = "غدا";
            }
        } else if ($hours >= 24) {
            $y = round($hours / 24);
            $x = $y . " ي";
        }
        return $x;
    }
}
