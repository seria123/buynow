<?php

namespace App\Http\Controllers\Seo;

use App\Http\Controllers\Controller;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\Category;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class SitemapController extends Controller
{
    public function index()
    {
        $sitemap = [];
        
        // Static pages - use URLs directly instead of route names
        $sitemap[] = $this->url(config('app.url') . '/', '1.0', 'daily');
        $sitemap[] = $this->url(route('products.index'), '1.0', 'daily');
        $sitemap[] = $this->url(config('app.url') . '/cart/page', '0.8', 'weekly');
        $sitemap[] = $this->url(route('orders.track.form'), '0.6', 'monthly');

        // Categories - skip if table doesn't exist or is empty
        try {
            $categories = Category::where('status', 'active')->orderBy('name')->get();
            foreach ($categories as $category) {
                if ($category->slug) {
                    $sitemap[] = $this->url(
                        route('products.index', ['category' => $category->slug]),
                        '0.8', 
                        'weekly',
                        $category->updated_at?->toIso8601String()
                    );
                }
            }
        } catch (\Exception $e) {
            // Skip categories if error
        }

        // Products - skip if table doesn't exist or is empty
        try {
            $products = Product::where('status', 'active')
                ->orderBy('updated_at', 'desc')
                ->limit(1000)
                ->get();
            
            foreach ($products as $product) {
                if ($product->slug) {
                    $sitemap[] = $this->url(
                        route('products.show', $product->slug),
                        '0.9',
                        'weekly',
                        $product->updated_at?->toIso8601String()
                    );
                }
            }
        } catch (\Exception $e) {
            // Skip products if error
        }

        // Announcements - skip if table doesn't exist or is empty
        try {
            $announcements = Announcement::where('status', 'published')
                ->orderBy('created_at', 'desc')
                ->limit(100)
                ->get();
            
            foreach ($announcements as $announcement) {
                if ($announcement->slug) {
                    $sitemap[] = $this->url(
                        route('announcements.show', $announcement->slug),
                        '0.6',
                        'monthly',
                        $announcement->updated_at?->toIso8601String()
                    );
                }
            }
        } catch (\Exception $e) {
            // Skip announcements if error
        }

        return $this->buildXml($sitemap);
    }

    private function url($loc, $priority, $changefreq, $lastmod = null)
    {
        return [
            'loc' => $loc,
            'lastmod' => $lastmod ?? now()->toIso8601String(),
            'priority' => $priority,
            'changefreq' => $changefreq,
        ];
    }

    private function buildXml($urls)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        foreach ($urls as $url) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($url['loc']) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . $url['lastmod'] . '</lastmod>' . "\n";
            $xml .= '    <changefreq>' . $url['changefreq'] . '</changefreq>' . "\n";
            $xml .= '    <priority>' . $url['priority'] . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }
        
        $xml .= '</urlset>' . "\n";
        
        return response($xml, 200, [
            'Content-Type' => 'application/xml',
            'charset' => 'utf-8',
        ]);
    }
}
