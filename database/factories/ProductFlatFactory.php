<?php

use Illuminate\Support\Facades\Storage;
use Webkul\Product\Models\ProductAttributeValue;
use Webkul\Product\Models\ProductFlat;
use Faker\Generator as Faker;


$factory->define(ProductFlat::class, function(Faker $faker, $data){

    $products = $data['product_id'];
    $fakeData = dummyFlatData($faker);
    $attributes = app('Webkul\Attribute\Repositories\AttributeRepository')->get();
    foreach ($attributes as $attribute) {

        if (! isset($fakeData[$attribute->code]) || (in_array($attribute->type, ['date', 'datetime']) && ! $fakeData[$attribute->code]))
            continue;

        if ($attribute->type == 'multiselect' || $attribute->type == 'checkbox') {
            $fakeData[$attribute->code] = implode(",", $fakeData[$attribute->code]);
        }

        if ($attribute->type == 'image' || $attribute->type == 'file') {
            $dir = 'product';
            if (gettype($fakeData[$attribute->code]) == 'object') {
                $fakeData[$attribute->code] = request()->file($attribute->code)->store($dir);
            } else {
                $fakeData[$attribute->code] = NULL;
            }
        }

        $attributeValue = [
            'product_id' => $products->id,
            'attribute_id' => $attribute->id,
            'value' => $fakeData[$attribute->code],
            'channel' => $attribute->value_per_channel ? $fakeData['channel'] : null,
            'locale' => $attribute->value_per_locale ? $fakeData['locale'] : null
        ];

        $attributeValue[ProductAttributeValue::$attributeTypeFields[$attribute->type]] = $attributeValue['value'];

        unset($attributeValue['value']);

        factory(\Webkul\Product\Models\ProductAttributeValue::class)->create($attributeValue);
    }
    $fakeImage = uploadImages($faker, ['product_id' => $products->id]);
    factory(\Webkul\Product\Models\ProductImage::class, 5)->create($fakeImage);
    createProductCategories(['product_id' => $products->id], $faker);
    return $fakeData;
});
function uploadImages($faker, $product)
{

    $filepath = storage_path('app/public/product/');

    Storage::makeDirectory('/product/'. $product['product_id']);

    $path = $faker->image($filepath. $product['product_id'], 800, 800, 'food', true, true);


    $pos = strpos($path, 'product');

    $imagePath = substr($path, $pos);

    $data = [
        'path' => $imagePath,
        'product_id' => $product['product_id']
    ];

    return $data;
}
function dummyFlatData($faker){
    $productName = $faker->text(10);
    $sku = substr(strtolower(str_replace(array('a','e','i','o','u'), '', $productName)), 0, 6);
    $productSku = str_replace(' ', '', $sku) . "-". str_replace(' ', '', $sku) . "-" . rand(1,9999999) . "-" . rand(1,9999999);
    $price = $faker->numberBetween($min = 0, $max = 500);
    $specialPrice = rand('0', $faker->numberBetween($min = 0, $max = 500));
    if ($specialPrice == 0) {
        $max = $price;
        $min = $price;
    } else {
        $max = $specialPrice;
        $min = $specialPrice;
    }

    $localeCode = core()->getCurrentLocale()->code;
    $channelCode = core()->getCurrentChannel()->code;
    return [
        'sku' => $productSku,
        'name' => $productName,
        'url_key' => $faker->unique(true)->word . '-' . rand(1,9999999),
        'new' => 1,
        'featured' => 1,
        'visible_individually' => 1,
        'min_price' => $min,
        'max_price' => $max,
        'status' => 1,
        'color' => 1,
        'price' => $price,
        'special_price' => 0,
        'special_price_from' => null,
        'special_price_to' => null,
        'width' => $faker->randomNumber(2),
        'height' => $faker->randomNumber(2),
        'depth' => $faker->randomNumber(2),
        'meta_title' => '',
        'meta_keywords' => '',
        'meta_description' => '',
        'weight' => $faker->randomNumber(2),
        'color_label' => $faker->colorName,
        'size' => 6,
        'size_label' => 'S',
        'short_description' => '<p>' . $faker->paragraph . '</p>',
        'description' => '<p>' . $faker->paragraph . '</p>',
        'channel' => $channelCode,
        'locale' => $localeCode,
    ];

}
function createProductCategories($product, $faker)
{
    $categories = Webkul\Category\Models\Category::all()->random(3);
    $filterableAttribute = ['11', '23', '24', '25'];

    foreach ($categories as $category) {
        if (! empty($category->translations) && count($category->translations) > 0) {
            foreach ($category->translations as $translation) {

                DB::table('product_categories')->insert([
                    'product_id' => $product['product_id'],
                    'category_id' => $translation->category_id,
                ]);

                foreach ($filterableAttribute as $categoryFilterableAttribute) {

                    $categoryExist = DB::table('category_filterable_attributes')->where('category_id',$translation->category_id)->count();

                    if ($categoryExist < 4) {
                        DB::table('category_filterable_attributes')->insert([
                            'attribute_id' => $categoryFilterableAttribute,
                            'category_id' => $translation->category_id,
                        ]);
                    }
                }
            }
        }
    }
}