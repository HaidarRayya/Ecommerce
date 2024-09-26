<?php

namespace App\Http\Controllers\seller;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\SellerProductResource;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Favorite;
use App\Models\OrderProducts;
use App\Models\ProductEvaluation;
use App\Models\PurchaseHistory;
use App\Models\SellerCategory;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {

        $product_name = $request->input("product_name");
        $category_name = $request->input("category_name");
        $price = $request->input("price");
        $category_type = $request->input("category_type");

        $products = Product::query();
        $products = $products->where('seller_id', '=', Auth::user()->id)->where('deleted_at', '=', null);
        $products->when($product_name, function ($query) use ($product_name) {
            $query->where('name', 'LIKE', '%' . $product_name . '%');
        })->when($category_name, function ($query) use ($category_name) {
            $query->where('category_name', 'LIKE', '%' . $category_name . '%');
        })->when($price, function ($query) use ($price) {
            $query->where('price', '<=', $price);
        })->when($category_type, function ($query) use ($category_type) {
            $query->where('category_type', '=', $category_type);
        });

        $products = $products->get();
        $products = SellerProductResource::collection($products);
        return response()->json([
            'products' => $products
        ]);
    }

    /**
     *
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $productData = $request->validate([
            'name' => 'required',
            'category_id' => 'required',
            'description' => 'required',
            'price' => 'required',
            'count' => 'required',
        ]);
        if ($request->hasFile('image'))
            $productData['image'] = $request->file('image')->store('productsImages', 'public');

        $seller_category = SellerCategory::where('id', '=', $productData['category_id'])->first();

        $category = Category::where('id', '=', $seller_category->category_id)->first();

        $seller_id = Auth::user()->id;
        $productData['seller_id'] = $seller_id;
        $seller = User::where('id', '=', $seller_id)->first();
        $productData['seller_name'] = $seller->firstName . " " . $seller->lastName;

        $productData['category_name'] = $category->name;
        $productData['category_type'] = $category->type;
        $product = Product::create($productData);
        $product = SellerProductResource::make($product);
        return  response()->json([
            'product' => $product
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        // $comments = $product->load('comments');
        //$comments = CommentResource::collection($comments);
        $product = SellerProductResource::make($product);
        return  response()->json([
            'product' => $product,
            //     'comments' =>  $comments
        ]);
    }

    public function update($product_id, Request $request)
    {
        $product = Product::where('id', '=', $product_id)->first();
        $productData = $request->validate([
            'name' => 'required',
            'description' => 'required',
            'price' => 'required',
            'count' => 'required',
        ]);


        if ($request->hasFile('image')  && $request->file('image') != substr($product->image, 6)) {
            Storage::disk('public')->delete($product->image);
            $productData['image'] = $request->file('image')->store('productsImages', 'public');
        }
        $productData['seller_id'] = Auth::user()->id;
        $product->update($productData);
        $newProduct = SellerProductResource::make(Product::where('id', '=', $product_id)->first());
        return  response()->json([
            "message" => "تم تعديل بنجاح",
            'product' => $newProduct

        ]);
    }

    public static function destroy($product_id)
    {
        $product = Product::where('id', '=', $product_id)->first();
        $comments = Comment::where('product_id', '=', $product->id)->get();
        foreach ($comments  as    $comment) {
            $comment->delete();
        }
        $cartProducts = Cart::where('product_id', '=', $product->id)->get();
        foreach ($cartProducts  as $cartProduct) {
            $cartProduct->delete();
        }
        $favoriteProducts = Favorite::where('product_id', '=', $product->id)->get();
        foreach ($favoriteProducts  as $favoriteProduct) {
            $favoriteProduct->delete();
        }
        $purchaseHistories = PurchaseHistory::where('product_id', '=', $product->id)->get();
        foreach ($purchaseHistories  as $purchaseHistory) {
            $purchaseHistory->delete();
        }
        $productEvaluations = ProductEvaluation::where('product_id', '=', $product->id)->get();
        foreach ($productEvaluations  as $productEvaluation) {
            $productEvaluation->delete();
        }

        $ordersProducts = OrderProducts::where('product_id', '=', $product->id)->get();
        if ($ordersProducts->isEmpty()) {
            $product->forceDelete();
        } else {
            $product->delete();
        }

        return  response()->json([
            'message' => 'تم الحذف بنجاح'
        ]);
    }
}