<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ImageFetchController extends Controller
{
    /**
     * Attempt to scrape the og:image from the product's URL and save it.
     * Also handles direct image URL submission (image_url field).
     */
    public function fetch(Product $product)
    {
        // Direct URL paste (from inline form on the card)
        if (request()->filled('image_url')) {
            $url = request()->input('image_url');
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                return back()->with('error', 'Invalid image URL.');
            }
            $product->update(['image' => $url]);
            return back()->with('success', 'Image saved for "' . $product->name . '".');
        }

        if (!$product->url) {
            return back()->with('error', 'No product URL set — paste an image URL instead.');
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent'      => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept-Language' => 'en-US,en;q=0.9',
                    'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ])
                ->get($product->url);

            $html = $response->body();
            $imageUrl = $this->extractImage($html, $product->url);

            if (!$imageUrl) {
                return back()->with('error', 'Could not auto-fetch the image (Amazon often blocks this). Right-click a product image on Amazon → "Copy image address", then paste it using the "Paste URL" box below the card.');
            }

            $product->update(['image' => $imageUrl]);
            return back()->with('success', 'Image fetched for "' . $product->name . '".');
        } catch (\Exception $e) {
            return back()->with('error', 'Could not reach the URL. Paste an image URL directly using the box below the card.');
        }
    }

    private function extractImage(string $html, string $sourceUrl): ?string
    {
        // 1. og:image meta tag (Amazon, Daraz, most e-commerce sites)
        if (preg_match('/<meta[^>]+(?:property=["\']og:image["\']|name=["\']og:image["\'])[^>]+content=["\']([^"\']+)["\']/', $html, $m)) {
            return $this->absoluteUrl($m[1], $sourceUrl);
        }
        if (preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+(?:property=["\']og:image["\']|name=["\']og:image["\'])/', $html, $m)) {
            return $this->absoluteUrl($m[1], $sourceUrl);
        }

        // 2. Amazon-specific: landingImage or imgBlkFront
        if (preg_match('/"large"\s*:\s*"(https:\/\/m\.media-amazon\.com\/images\/[^"]+)"/', $html, $m)) {
            return $m[1];
        }
        if (preg_match('/data-old-hires=["\']([^"\']+)["\']/', $html, $m)) {
            return $this->absoluteUrl($m[1], $sourceUrl);
        }
        if (preg_match('/id=["\']landingImage["\'][^>]+src=["\']([^"\']+)["\']/', $html, $m)) {
            return $this->absoluteUrl($m[1], $sourceUrl);
        }

        // 3. twitter:image
        if (preg_match('/<meta[^>]+(?:name=["\']twitter:image["\'])[^>]+content=["\']([^"\']+)["\']/', $html, $m)) {
            return $this->absoluteUrl($m[1], $sourceUrl);
        }

        return null;
    }

    private function absoluteUrl(string $url, string $base): string
    {
        if (str_starts_with($url, 'http')) return $url;
        $parsed = parse_url($base);
        $scheme = $parsed['scheme'] ?? 'https';
        $host   = $parsed['host'] ?? '';
        return $scheme . '://' . $host . '/' . ltrim($url, '/');
    }
}
