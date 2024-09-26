<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use App\Models\ConfirmationCode;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderProducts;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $notifictions = Notification::where('user_id', '=', Auth::user()->id)
            ->orderByDesc('date')
            ->limit(50)
            ->get();
        $notifictions = NotificationResource::collection($notifictions);
        return response()->json([
            'notifictions' => $notifictions
        ]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function test()
    {
        $confirmationCodes = ConfirmationCode::all();
        foreach ($confirmationCodes as $confirmationCode) {
            if (now()->diffInHours($confirmationCode->created_at) >= 1) {
                $confirmationCode->delete();
            }
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */

    public  function addNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'descripation' => 'required',
            'redirection' => 'required'
        ], [
            'user_id.required' => 'يجب اضافة رقم الزبون',
            'descripation.required' => 'يجب اضافة وصف الاشعار',
            'redirection.required' => 'يجب اضافة اعادة التوجيه ',
        ]);
        if ($validator->fails()) {
            throw ValidationException::withMessages([
                'message' => "البيانات المدخلة خاطئة",
                'errors' => $validator->errors()
            ]);
        }
        $notification = [
            'user_id' => $request->user_id,
            'descripation' => $request->descripation,
            'date' => now(),
            'redirection' => $request->redirection,
        ];
        if ($request->seller_id) {
            $notification['seller_id'] = $request->redirection;
        }
        Notification::create($notification);
        return response(200);
    }
    public static function store($data)
    {
        Notification::create($data);
        $notifictions = Notification::whereDate('created_at', '<=', now()->subDays(14))->get();
        foreach ($notifictions as $notifiction) {
            $notifiction->delete();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function reading($id)
    {
        $notifiction = Notification::find($id);
        $notifiction->update(["reading" => 1]);
    }
    public function readingAll()
    {
        $notifictions = Notification::where('user_id', '=', Auth::user()->id)->get();

        foreach ($notifictions as $notifiction) {
            $notifiction->update(["reading" => 1]);
        }
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $notification = Notification::find($id)->first();
        $notification->delete();
        return response()->json([
            'message' => 'تم حذف الاشعار بنجاح'
        ]);
    }
    public function destroyAll()
    {
        $notification = Notification::where('user_id', '=', Auth::user()->id)->get();
        foreach ($notification as $x) {
            $x->delete();
        }
        return response()->json([
            'message' => 'تم حذف الاشعارات بنجاح'
        ]);
    }
}
