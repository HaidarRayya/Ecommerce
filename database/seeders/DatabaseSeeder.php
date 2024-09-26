<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Product;
use App\Models\User;
use App\Models\Favorite;
use App\Models\Operation;
use App\Models\RequestCategory;
use App\Models\SellerCategory;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        User::create([
            'firstName' => 'حيدر',
            'lastName' => 'سليمان',
            'email' => 'haidarsul@gmail.com',
            'address' => 'lattakia',
            'role' => 'admin',
            'phoneNumber' => 465423,
            'password' => Hash::make(12345678),
        ]);
        User::create([
            'firstName' => 'حيدر',
            'lastName' => 'ريا',
            'email' => 'haidarrayya@gmail.com',
            'address' => 'lattakia',
            'role' => 'seller',
            'phoneNumber' => 23523,
            'password' => Hash::make(12345678),
        ]);
        User::create([
            'firstName' => 'حيدر',
            'lastName' => 'ريا',
            'email' => 'haidarrayya2@gmail.com',
            'address' => 'lattakia',
            'role' => 'customer',
            'phoneNumber' => 2323523,
            'password' => Hash::make(12345678),
        ]);


        Category::create([
            'name' => 'Tv',
            'type' => 0
        ]);
        Category::create([
            'name' => 'Tv',
            'type' => 1
        ]);
        Category::create([
            'name' => 'laptop',
            'type' => 0
        ]);
        Category::create([
            'name' => 'laptop',
            'type' => 1
        ]);
        SellerCategory::create([
            'seller_id' => 2,
            'category_id' => 1,
        ]);

        SellerCategory::create([
            'seller_id' => 2,
            'category_id' => 2,
        ]);

        Product::create([
            'name' => 'asus1',
            'category_id' => 1,
            'price' => 10,
            'count' => 12,
            'image' => "img1.png",
            'seller_id' => 2,
            'category_type' => 0,
            'category_name' => 'laptop',
            'seller_name' => 'حيدر',
        ]);
        Product::create([
            'name' => 'lenovo1',
            'category_id' => 1,
            'price' => 10,
            'count' => 12,
            'image' => "img2.jpg",
            'seller_id' => 2,
            'category_type' => 0,
            'category_name' => 'laptop',
            'seller_name' => 'حيدر',
        ]);
        Product::create([
            'name' => 'asus2',
            'category_id' => 1,
            'price' => 10,
            'count' => 12,
            'image' => "img3.jpg",
            'seller_id' => 2,
            'category_type' => 1,
            'category_name' => 'laptop',
            'seller_name' => 'حيدر',
        ]);
        Product::create([
            'name' => 'lenovo2',
            'category_id' => 1,
            'price' => 10,
            'count' => 12,
            'image' => "img4.jpg",
            'seller_id' => 2,
            'category_type' => 0,
            'category_name' => 'laptop',
            'seller_name' => 'حيدر',
        ]);
        Product::create([
            'name' => 'lenovo2',
            'category_id' => 1,
            'price' => 10,
            'count' => 12,
            'image' => "img5.jpg",
            'seller_id' => 2,
            'category_type' => 1,
            'category_name' => 'laptop',
            'seller_name' => 'علي',
        ]);
        Product::create([
            'name' => 'lenovo3',
            'category_id' => 1,
            'price' => 10,
            'count' => 12,
            'image' => "img6.jpg",
            'seller_id' => 2,
            'category_type' => 0,
            'category_name' => 'tv',
            'seller_name' => 'علي',
        ]);
    }
}
