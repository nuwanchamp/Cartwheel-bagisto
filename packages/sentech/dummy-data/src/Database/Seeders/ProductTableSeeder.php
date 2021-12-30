<?php
namespace  Sentech\DummyData\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Webkul\Product\Models\Product;
use Webkul\Product\Models\ProductFlat;
use Webkul\Product\Models\ProductInventory;

class ProductTableSeeder extends Seeder
{
    public function run()
    {
       DB::table('products')->delete();
        DB::table('product_flat')->delete();
        DB::table('product_inventories')->delete();
        DB::table('product_images')->delete();
        DB::table('product_attribute_values')->delete();

        Product::factory()->count(4)->create()->each(function($product){
            $faker = \Faker\Factory::create();
           $product->update(["type"=> "simple"]);
           factory(ProductFlat::class, 1)->create(["product_id" => $product]);
           ProductInventory::factory()->create(["product_id" => $product->id, "inventory_source_id" => 1]);
        });
    }
}