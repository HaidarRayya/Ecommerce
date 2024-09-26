<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    public static function accept($mailData, $to)
    {
        try {
            $GLOBALS['x'] = $to;
            Mail::send('email', [
                'data' => $mailData
            ], function ($message) {
                $message->to($GLOBALS['x']);
                $message->subject("قبول طلب التسجيل ");
            });
        } catch (Exception $ex) {
        }
    }
    public static function reject($mailData, $to)
    {
        try {
            $GLOBALS['x'] = $to;
            Mail::send('email', [
                'data' => $mailData
            ], function ($message) {
                $message->to($GLOBALS['x']);
                $message->subject("رفض طلب التسجيل");
            });
        } catch (Exception) {
        }
    }
    public static function acceptOrder($mailData, $to)
    {
        try {
            $GLOBALS['x'] = $to;
            Mail::send('email', [
                'data' => $mailData
            ], function ($message) {
                $message->to($GLOBALS['x']);
                $message->subject("قبول  الطلبية ");
            });
        } catch (Exception $ex) {
        }
    }
    public static function rejectOrder($mailData, $to)
    {
        try {
            $GLOBALS['x'] = $to;
            Mail::send('email', [
                'data' => $mailData
            ], function ($message) {
                $message->to($GLOBALS['x']);
                $message->subject("رفض الطلبية ");
            });
        } catch (Exception) {
        }
    }
    public static function changePassword($mailData, $to)
    {
        try {
            $GLOBALS['x'] = $to;
            Mail::send('email', [
                'data' => $mailData
            ], function ($message) {
                $message->to($GLOBALS['x']);
                $message->subject("اعادة تعيين كلمة المرور");
            });
        } catch (Exception) {
        }
    }
    public static function sendConfirmationCode($mailData, $to)
    {
        try {
            $GLOBALS['x'] = $to;
            Mail::send('email', [
                'data' => $mailData
            ], function ($message) {
                $message->to($GLOBALS['x']);
                $message->subject("رمز تأكيد");
            });
        } catch (Exception) {
        }
    }
}
