<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\EmailController;
use App\Models\User;
use Illuminate\Http\Request;

class ManagePointRetailer extends Controller
{
    public function accept($id, Request $request)
    {
        $pointRetailer = User::where('id', '=', $id)->first();
        $pointRetailer->update(['status' => 'accept']);
        $data = [
            'subject' => "قبول طلب التسجيل  ",
            "body" =>  "مرحبا " . " " . $pointRetailer->firstname . " "  . $pointRetailer->lastname . " " .  " تم قبول طلب التسجيل  "
        ];
        EmailController::accept($data, $pointRetailer->email);
        return response()->json([
            'message' => 'تم قبول الطلب بنجاح'
        ]);
    }
    public function reject($id, Request $request)
    {
        $pointRetailer = User::where('id', '=', $id)->first();
        $data = [
            'subject' => " رفض طلب التسجيل ",
            "body" =>  "مرحبا " . " " . $pointRetailer->firstname . " "  . $pointRetailer->lastname . " " .  " تم قبول طلب التسجيل  "
        ];
        EmailController::reject($data, $pointRetailer->email);
        $pointRetailer->delete();

        return response()->json([
            'message' => 'تم حذف الطلب بنجاح'
        ]);
    }
    public function getAllRequests()
    {
        $requestsPointRetailer = User::where('status', '=', "waiting")->get();

        return response()->json([
            'requestsPointRetailer' => $requestsPointRetailer
        ]);
    }
}
