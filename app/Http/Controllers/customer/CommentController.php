<?php

namespace App\Http\Controllers\customer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\NotificationController;
use App\Http\Resources\CommentResource;
use App\Http\Resources\SellerProductResource;
use App\Models\Comment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($product_id)
    {
        $comments = Comment::where('product_id', '=', $product_id)->orderByDesc('updated_at')->get();
        $comments = CommentResource::collection($comments);

        return response()->json([
            "comments" => $comments
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store($product_id, Request $request)
    {
        $request->validate([
            'description' => 'required',
        ]);

        $commentData = [
            'user_id' => Auth::user()->id,
            'product_id' => $product_id,
            'description' =>  $request->description,
        ];
        $customer = User::find(Auth::user()->id);

        if ($customer->role = 'customer') {
            $product = Product::find($product_id);
            $seller = User::find($product->seller_id);
            $notification = [
                'user_id' => $seller->id,
                "descripation" =>  "قام الزبون " . " " . $customer->firstName . " "  . $customer->lastName . " " .  ' بالتعليق على المنتج  ' . $product->name,
                'date' => now(),
                'redirection' => '/seller_comments',
                'data' => SellerProductResource::make($product)->toJson()
            ];
            NotificationController::store($notification);
        }
        Comment::create($commentData);
        return response(200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function update($product_id, $comment_id, Request $request)
    {
        $request->validate([
            'description' => 'required'
        ]);
        $comment = Comment::where('id', '=', $comment_id)->first();
        if (Auth::user()->id == $comment->user_id) {
            $comment->update(['description' => $request->description]);
            return response(200);
        } else {
            return  response()->json([
                'message' => "لا يمكنك القيام بهذه العملية"
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($product_id, $comment_id)
    {
        $comment = Comment::where('id', '=', $comment_id)->first();
        if (Auth::user()->id == $comment->user_id) {
            $comment->delete();
            return response(200);
        } else {
            return  response()->json([
                'message' => "لا يمكنك القيام بهذه العملية"
            ]);
        }
    }
}