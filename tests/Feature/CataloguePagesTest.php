<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Models\Catalogue\Attribute;
use App\Models\Catalogue\AttributeFamily;
use App\Models\Catalogue\AttributeType;
use App\Models\Catalogue\Brand;
use App\Models\Catalogue\Category;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\ProductAttributeValue;
use App\Models\Catalogue\ProductVariant;
use App\Models\Catalogue\ProductVariantOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CataloguePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_page_exposes_filters_and_attributes(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'name' => 'Phones',
            'slug' => 'phones',
            'is_active' => true,
            'creator_id' => $user->id,
        ]);

        $brand = Brand::create([
            'name' => 'Acme',
            'slug' => 'acme',
            'is_active' => true,
            'creator_id' => $user->id,
        ]);

        $family = AttributeFamily::create([
            'name' => 'Device Specs',
            'slug' => 'device-specs',
            'is_active' => true,
            'creator_id' => $user->id,
        ]);

        $attribute = Attribute::create([
            'name' => 'Color',
            'slug' => 'color',
            'attribute_family_id' => $family->id,
            'type' => AttributeType::Predefined,
            'is_active' => true,
            'creator_id' => $user->id,
        ]);

        $product = Product::create([
            'name' => 'Test Phone',
            'slug' => 'test-phone-'.Str::random(8),
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'attribute_family_id' => $family->id,
            'price' => 999.99,
            'compare_price' => 1099.99,
            'status' => ProductStatus::Approved,
            'published' => true,
            'creator_id' => $user->id,
        ]);

        ProductAttributeValue::create([
            'product_id' => $product->id,
            'attribute_id' => $attribute->id,
            'value' => 'Black',
        ]);

        $response = $this->get('/products');

        $response->assertOk();
        $response->assertInertia(
            fn (Assert $page) => $page
                ->component('Products/Index')
                ->has(
                    'products',
                    1,
                    fn (Assert $productAssert) => $productAssert
                        ->where('id', $product->id)
                        ->where('name', 'Test Phone')
                        ->where('category.name', $category->name)
                        ->where('brand.name', $brand->name)
                        ->etc()
                )
                ->has(
                    'attributes',
                    1,
                    fn (Assert $attributeAssert) => $attributeAssert
                        ->where('id', $attribute->id)
                        ->where('name', $attribute->name)
                        ->where('values', fn ($values) => in_array('Black', is_array($values) ? $values : $values->all()))
                        ->etc()
                )
                ->has(
                    'filterCategories',
                    fn (Assert $filterCategories) => $filterCategories
                        ->where('0.name', 'Phones')
                        ->etc()
                )
        );
    }

    public function test_products_page_lists_variants(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'name' => 'Accessories',
            'slug' => 'accessories',
            'is_active' => true,
            'creator_id' => $user->id,
        ]);

        $brand = Brand::create([
            'name' => 'Nova',
            'slug' => 'nova',
            'is_active' => true,
            'creator_id' => $user->id,
        ]);

        $family = AttributeFamily::create([
            'name' => 'Wearables',
            'slug' => 'wearables',
            'is_active' => true,
            'creator_id' => $user->id,
        ]);

        $attribute = Attribute::create([
            'name' => 'Size',
            'slug' => 'size',
            'attribute_family_id' => $family->id,
            'type' => AttributeType::Predefined,
            'is_active' => true,
            'creator_id' => $user->id,
        ]);

        $product = Product::create([
            'name' => 'Smart Band',
            'slug' => 'smart-band-'.Str::random(8),
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'attribute_family_id' => $family->id,
            'price' => 149.99,
            'compare_price' => 199.99,
            'status' => ProductStatus::Approved,
            'published' => true,
            'creator_id' => $user->id,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Large',
            'price' => 159.99,
            'compare_price' => 199.99,
            'quantity' => 25,
            'is_default' => true,
        ]);

        ProductVariantOption::create([
            'product_variant_id' => $variant->id,
            'attribute_id' => $attribute->id,
            'value' => 'Large',
        ]);

        $response = $this->get('/products');

        $response->assertOk();
        $response->assertInertia(
            fn (Assert $page) => $page
                ->component('Products/Index')
                ->has('filterCategories')
                ->has('brands')
                ->has(
                    'priceRange',
                    fn (Assert $priceRange) => $priceRange
                        ->where('min', fn ($min) => (float) $min === 149.99)
                        ->where('max', fn ($max) => (float) $max === 149.99)
                )
                ->has('products', 2)
                ->where('products.0.name', 'Smart Band')
                ->where('products.0.is_variant', false)
                ->where('products.1.is_variant', true)
                ->where('products.1.variant_badges.0.value', 'Large')
                ->has(
                    'filters',
                    fn (Assert $filters) => $filters
                        ->where('category', null)
                        ->where('brands', [])
                        ->where('price.min', fn ($min) => (float) $min === 149.99)
                        ->where('price.max', fn ($max) => (float) $max === 149.99)
                        ->etc()
                )
        );
    }

    public function test_products_page_filters_by_category_and_brand(): void
    {
        $user = User::factory()->create();

        $categoryA = Category::create([
            'name' => 'Shoes',
            'slug' => 'shoes',
            'is_active' => true,
            'creator_id' => $user->id,
        ]);

        $categoryB = Category::create([
            'name' => 'Bags',
            'slug' => 'bags',
            'is_active' => true,
            'creator_id' => $user->id,
        ]);

        $brandA = Brand::create([
            'name' => 'Stride',
            'slug' => 'stride',
            'is_active' => true,
            'creator_id' => $user->id,
        ]);

        $brandB = Brand::create([
            'name' => 'CarryAll',
            'slug' => 'carry-all',
            'is_active' => true,
            'creator_id' => $user->id,
        ]);

        $productMatches = Product::create([
            'name' => 'Runner Max',
            'slug' => 'runner-max-'.Str::random(8),
            'category_id' => $categoryA->id,
            'brand_id' => $brandA->id,
            'price' => 89.99,
            'compare_price' => 119.99,
            'status' => ProductStatus::Approved,
            'published' => true,
            'creator_id' => $user->id,
        ]);

        Product::create([
            'name' => 'City Tote',
            'slug' => 'city-tote-'.Str::random(8),
            'category_id' => $categoryB->id,
            'brand_id' => $brandB->id,
            'price' => 129.99,
            'compare_price' => 149.99,
            'status' => ProductStatus::Approved,
            'published' => true,
            'creator_id' => $user->id,
        ]);

        $response = $this->get('/products?category=shoes&brands[]='.$brandA->id);

        $response->assertOk();
        $response->assertInertia(
            fn (Assert $page) => $page
                ->component('Products/Index')
                ->has(
                    'products',
                    1,
                    fn (Assert $productAssert) => $productAssert
                        ->where('id', $productMatches->id)
                        ->etc()
                )
                ->has(
                    'filters',
                    fn (Assert $filters) => $filters
                        ->where('category', 'shoes')
                        ->where('brands', [$brandA->id])
                        ->etc()
                )
        );
    }

    public function test_navigation_share_includes_children_products_and_variants(): void
    {
        Cache::flush();

        $user = User::factory()->create();

        $parent = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'sort_order' => 1,
            'is_active' => true,
            'creator_id' => $user->id,
        ]);

        $child = Category::create([
            'name' => 'Phones',
            'slug' => 'phones',
            'parent_id' => $parent->id,
            'sort_order' => 2,
            'is_active' => true,
            'creator_id' => $user->id,
        ]);

        $product = Product::create([
            'name' => 'Galaxy Prime',
            'slug' => 'galaxy-prime-'.Str::random(6),
            'category_id' => $parent->id,
            'price' => 599.99,
            'status' => ProductStatus::Approved,
            'published' => true,
            'creator_id' => $user->id,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => '128 GB',
            'price' => 629.99,
            'quantity' => 10,
            'is_default' => true,
        ]);

        $response = $this->get('/products');

        $response->assertInertia(
            fn (Assert $page) => $page
                ->has('categories', 1, fn (Assert $category) => $category
                    ->where('id', $parent->id)
                    ->has('children', 1, fn (Assert $childAssert) => $childAssert
                        ->where('id', $child->id)
                        ->etc()
                    )
                    ->has('products', 1, fn (Assert $productAssert) => $productAssert
                        ->where('id', $product->id)
                        ->has('variants', 1, fn (Assert $variantAssert) => $variantAssert
                            ->where('id', $variant->id)
                            ->where('name', '128 GB')
                            ->etc()
                        )
                        ->etc()
                    )
                    ->etc()
                )
        );
    }
}
