<?php

namespace App\Http\Controllers\Pages;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Catalogue\Category;
use App\Models\Catalogue\Product;
use App\Models\Sales\Refund;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;

class PagesController extends Controller
{
    public function index(): Response
    {
        // Get all active categories with children for navigation dropdown
        $categories = $this->getCategoriesForNavigation();

        // Get featured products with proper transformation
        $featuredProducts = $this->getFeaturedProducts();

        // Get featured products with proper transformation
        $featuredProducts = $this->getFeaturedProducts();

        // Get latest products for general display
        $latestProducts = $this->getLatestProducts();

        // Get ads for homepage
        $homepageAds = Ad::query()
            ->active()
            ->valid()
            ->byPosition('home')
            ->ordered()
            ->take(5)
            ->get()
            ->map(fn ($ad) => [
                'id' => $ad->id,
                'title' => $ad->title,
                'subtitle' => $ad->subtitle,
                'image' => $ad->image ? asset('storage/' . $ad->image) : null,
                'mobile_image' => $ad->mobile_image ? asset('storage/' . $ad->mobile_image) : null,
                'link' => $ad->link,
                'type' => $ad->type,
            ]);

        // Get products on sale (with compare_price set - this acts as special price)
        $saleProducts = $this->getSaleProducts();

        // Extract unique parent categories from featured products
        $featuredCategories = Category::where('is_active', true)
            ->whereNotNull('parent_id')
            ->whereHas('products', function ($query) {
                $query->where('status', ProductStatus::Approved)
                      ->where('published', true)
                      ->where('is_featured', true);
            })
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        // Mock flash sale end time (in real app, this would come from database)
        $flashSaleEndTime = Carbon::now()->addHours(12)->toIso8601String();

        return Inertia::render('Index', [
            'categories' => $categories,
            'featuredCategories' => $featuredCategories,
            'featuredProducts' => $featuredProducts,
            'latestProducts' => $latestProducts,
            'homepageAds' => $homepageAds,
            'saleProducts' => $saleProducts,
            'flashSaleEndTime' => $flashSaleEndTime,
        ]);
    }

    /**
     * Returns & Refunds Policy page
     */
    public function returns(): Response
    {
        $refundPolicies = [
            [
                'title' => 'Return Window',
                'description' => 'You can return most items within 14 days of delivery. Items must be unused and in original packaging.',
            ],
            [
                'title' => 'Eligible Items',
                'description' => 'Products must be in original condition with all tags attached. Sealed items must remain unopened.',
            ],
            [
                'title' => 'Refund Process',
                'description' => 'Refunds are processed within 5-7 business days after we receive and inspect your return.',
            ],
            [
                'title' => 'Refund Method',
                'description' => 'Refunds are credited to your original payment method. For M-Pesa payments, refunds are sent to your registered number.',
            ],
            [
                'title' => 'Return Shipping',
                'description' => 'Customers are responsible for return shipping costs unless the item is defective or incorrect.',
            ],
            [
                'title' => 'Non-Returnable Items',
                'description' => 'Personal care items, opened software, and digital downloads cannot be returned.',
            ],
        ];

        return Inertia::render('Returns', [
            'policies' => $refundPolicies,
        ]);
    }

    /**
     * Shipping Information page
     */
    public function shipping(): Response
    {
        $shippingInfo = [
            'deliveryTimes' => [
                ['location' => 'Nairobi', 'time' => 'Same day - 2 business days', 'cost' => 'KSh 0 - 300'],
                ['location' => 'Kisumu, Mombasa, Nakuru', 'time' => '2-4 business days', 'cost' => 'KSh 300 - 500'],
                ['location' => 'Other Major Cities', 'time' => '3-5 business days', 'cost' => 'KSh 400 - 600'],
                ['location' => 'Rural Areas', 'time' => '5-7 business days', 'cost' => 'KSh 500 - 800'],
            ],
            'shippingMethods' => [
                ['name' => 'Standard Delivery', 'description' => 'Delivery within 3-5 business days'],
                ['name' => 'Express Delivery', 'description' => 'Delivery within 1-2 business days (Nairobi only)'],
                ['name' => 'Pickup Point', 'description' => 'Collect from our partner locations'],
            ],
            'freeShippingThreshold' => 10000,
            'orderTracking' => 'You can track your order using the tracking number sent to your email.',
        ];

        return Inertia::render('Shipping', [
            'shippingInfo' => $shippingInfo,
        ]);
    }

    /**
     * FAQ page
     */
    public function faq(): Response
    {
        $faqs = [
            [
                'question' => 'How do I track my order?',
                'answer' => 'You can track your order by clicking on the "Track Order" link in your confirmation email or by visiting our tracking page with your order number.',
            ],
            [
                'question' => 'What payment methods do you accept?',
                'answer' => 'We accept M-Pesa, Visa, Mastercard, and mobile banking. All transactions are secure and encrypted.',
            ],
            [
                'question' => 'How long does delivery take?',
                'answer' => 'Delivery times vary by location. Nairobi: 1-2 days, Major cities: 2-4 days, Other areas: 3-7 days.',
            ],
            [
                'question' => 'Can I return a product?',
                'answer' => 'Yes, you can return most items within 14 days of delivery if they are unused and in original packaging. Some items like personal care products cannot be returned.',
            ],
            [
                'question' => 'How do I contact customer support?',
                'answer' => 'You can reach us via email at support@buynow.co.ke, phone at +254 700 000 000, or through our live chat.',
            ],
            [
                'question' => 'Do you offer warranty?',
                'answer' => 'Yes, all electronics come with manufacturer warranty. Extended warranties are available for purchase on select items.',
            ],
            [
                'question' => 'How do I change my order?',
                'answer' => 'To modify your order, please contact us within 1 hour of placing the order. We cannot guarantee changes after this period.',
            ],
            [
                'question' => 'Is my personal information secure?',
                'answer' => 'Yes, we take data privacy seriously. Your information is encrypted and never shared with third parties.',
            ],
        ];

        return Inertia::render('Faq', [
            'faqs' => $faqs,
        ]);
    }

    /**
     * Get all active categories with children for navigation
     */
    private function getCategoriesForNavigation(): array
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->with(['activeChildren' => function ($query) {
                $query->with(['activeChildren']);
            }])
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'icon', 'parent_id']);

        return $categories->map(function (Category $category) {
            return $this->formatCategoryWithChildren($category);
        })->toArray();
    }

    /**
     * Format category with children recursively
     */
    private function formatCategoryWithChildren(Category $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'icon' => $category->icon,
            'children' => $category->activeChildren->map(fn (Category $child) => $this->formatCategoryWithChildren($child))->values()->toArray(),
        ];
    }

    /**
     * Get featured products with proper transformation
     */
    private function getFeaturedProducts(): array
    {
        $products = Product::where('status', ProductStatus::Approved)
            ->where('published', true)
            ->where('is_featured', true)
            ->whereHas('category', function ($query) {
                $query->where('is_active', true);
            })
            ->with(['category.parent', 'brand', 'variants', 'media'])
            ->limit(12)
            ->get();

        return $products->map(fn($product) => $this->transformProduct($product))->toArray();
    }

    /**
     * Get latest products with proper transformation
     */
    private function getLatestProducts(): array
    {
        $products = Product::where('status', ProductStatus::Approved)
            ->where('published', true)
            ->whereHas('category', function ($query) {
                $query->where('is_active', true);
            })
            ->with(['category.parent', 'brand', 'variants', 'media'])
            ->latest()
            ->limit(20)
            ->get();

        return $products->map(fn($product) => $this->transformProduct($product))->toArray();
    }

    /**
     * Get sale products with proper transformation
     */
    private function getSaleProducts(): array
    {
        $products = Product::where('status', ProductStatus::Approved)
            ->where('published', true)
            ->whereNotNull('compare_price')
            ->whereHas('category', function ($query) {
                $query->where('is_active', true);
            })
            ->with(['category.parent', 'brand', 'variants', 'media'])
            ->limit(12)
            ->get();

        return $products->map(fn($product) => $this->transformProduct($product))->toArray();
    }

    /**
     * Transform product for frontend display
     */
    private function transformProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => $product->price,
            'compare_price' => $product->compare_price,
            'thumbnail_url' => $product->thumbnail_url,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ] : null,
            'brand' => $product->brand ? [
                'id' => $product->brand->id,
                'name' => $product->brand->name,
            ] : null,
            'stats' => [
                'total_stock' => $product->getTotalStock(),
                'variant_count' => $product->variants->count(),
                'has_variants' => $product->hasVariants(),
            ],
            'is_featured' => $product->is_featured,
            'is_variant' => false,
        ];
    }
}
