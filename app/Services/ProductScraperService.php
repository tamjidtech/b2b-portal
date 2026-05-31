<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Scrapes a product page (Amazon.sa, Daraz.com.bd, etc.) and extracts
 * name, image URL, and price. Returns null fields if not found.
 */
class ProductScraperService
{
    private const HEADERS = [
        'User-Agent'      => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Accept-Language' => 'en-US,en;q=0.9',
        'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    ];

    // Approximate FX rates (BDT). Update via config later if needed.
    private const SAR_TO_BDT = 32.5;
    private const USD_TO_BDT = 122.0;

    public function fetch(string $url): array
    {
        $result = [
            'name'        => null,
            'image'       => null,
            'price_local' => null,  // raw price string in local currency
            'price_bdt'   => null,  // converted to BDT
            'currency'    => null,
            'category'    => null,
            'source'      => $this->detectSource($url),
            'error'       => null,
        ];

        try {
            $response = Http::timeout(12)->withHeaders(self::HEADERS)->get($url);
            if (!$response->successful()) {
                $result['error'] = "Page returned {$response->status()}";
                return $result;
            }
            $html = $response->body();
        } catch (\Exception $e) {
            $result['error'] = 'Could not reach the URL.';
            return $result;
        }

        $result['name']  = $this->extractName($html);
        $result['image'] = $this->extractImage($html, $url);
        [$priceLocal, $currency] = $this->extractPrice($html, $result['source']);
        $result['price_local'] = $priceLocal;
        $result['currency']    = $currency;
        $result['price_bdt']   = $this->convertToBdt($priceLocal, $currency);
        $result['category']    = $this->guessCategory($html, $result['name'] ?? '');

        if (!$result['name'] && !$result['image'] && !$result['price_local']) {
            $result['error'] = 'Could not extract any data — site may be blocking server requests. Try pasting an image URL manually.';
        }

        return $result;
    }

    private function detectSource(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST) ?? '';
        if (str_contains($host, 'amazon')) return 'amazon';
        if (str_contains($host, 'daraz')) return 'daraz';
        if (str_contains($host, 'noon')) return 'noon';
        return 'generic';
    }

    private function extractName(string $html): ?string
    {
        // Try Open Graph title
        if (preg_match('/<meta[^>]+property=["\']og:title["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
            return $this->cleanText($m[1]);
        }
        // Try Amazon productTitle id
        if (preg_match('/<span[^>]+id=["\']productTitle["\'][^>]*>([^<]+)<\/span>/i', $html, $m)) {
            return $this->cleanText($m[1]);
        }
        // Try Twitter title
        if (preg_match('/<meta[^>]+name=["\']twitter:title["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
            return $this->cleanText($m[1]);
        }
        // Generic <title> as last resort
        if (preg_match('/<title>([^<]+)<\/title>/i', $html, $m)) {
            $title = $this->cleanText($m[1]);
            // Strip common suffixes
            $title = preg_replace('/\s*[:|–\-]\s*Amazon\.sa.*/i', '', $title);
            $title = preg_replace('/\s*[:|–\-]\s*Daraz.*/i', '', $title);
            return $title;
        }
        return null;
    }

    private function extractImage(string $html, string $pageUrl): ?string
    {
        // og:image
        if (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
            return $this->absoluteUrl($m[1], $pageUrl);
        }
        // Amazon "large" JSON key
        if (preg_match('/"large":"(https:[^"]+\.(?:jpg|jpeg|png|webp))"/i', $html, $m)) {
            return str_replace('\\/', '/', $m[1]);
        }
        // Amazon data-old-hires
        if (preg_match('/data-old-hires=["\']([^"\']+)["\']/i', $html, $m)) {
            return $m[1];
        }
        // Amazon landingImage id
        if (preg_match('/id=["\']landingImage["\'][^>]+src=["\']([^"\']+)["\']/i', $html, $m)) {
            return $m[1];
        }
        // twitter:image
        if (preg_match('/<meta[^>]+name=["\']twitter:image["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
            return $this->absoluteUrl($m[1], $pageUrl);
        }
        return null;
    }

    private function extractPrice(string $html, string $source): array
    {
        // Amazon: <span class="a-offscreen">SAR 99.00</span> or BDT
        if ($source === 'amazon') {
            if (preg_match('/<span[^>]+class=["\']a-offscreen["\'][^>]*>([^<]+)<\/span>/i', $html, $m)) {
                return $this->parsePrice($m[1]);
            }
            if (preg_match('/"priceAmount":\s*([0-9.]+)/i', $html, $m)) {
                return [(float)$m[1], 'SAR'];
            }
        }
        // Daraz: meta itemprop="price"
        if ($source === 'daraz') {
            if (preg_match('/<meta[^>]+itemprop=["\']price["\'][^>]+content=["\']([0-9.]+)["\']/i', $html, $m)) {
                return [(float)$m[1], 'BDT'];
            }
            if (preg_match('/"price":\s*"?([0-9.]+)"?/i', $html, $m)) {
                return [(float)$m[1], 'BDT'];
            }
        }
        // Generic: Open Graph price
        if (preg_match('/<meta[^>]+property=["\']product:price:amount["\'][^>]+content=["\']([0-9.]+)["\']/i', $html, $m)) {
            $currency = 'USD';
            if (preg_match('/<meta[^>]+property=["\']product:price:currency["\'][^>]+content=["\']([A-Z]{3})["\']/i', $html, $cm)) {
                $currency = $cm[1];
            }
            return [(float)$m[1], $currency];
        }
        return [null, null];
    }

    private function parsePrice(string $raw): array
    {
        $raw = trim($raw);
        $currency = null;
        if (preg_match('/SAR|﷼/i', $raw))      $currency = 'SAR';
        elseif (preg_match('/BDT|৳|Tk/i', $raw)) $currency = 'BDT';
        elseif (preg_match('/USD|\$/i', $raw))   $currency = 'USD';

        if (preg_match('/([0-9][0-9,]*\.?[0-9]*)/', $raw, $pm)) {
            return [(float)str_replace(',', '', $pm[1]), $currency];
        }
        return [null, $currency];
    }

    private function convertToBdt(?float $price, ?string $currency): ?float
    {
        if ($price === null) return null;
        return match ($currency) {
            'SAR' => round($price * self::SAR_TO_BDT, 2),
            'USD' => round($price * self::USD_TO_BDT, 2),
            'BDT' => $price,
            default => $price,
        };
    }

    private function guessCategory(string $html, string $name): ?string
    {
        $haystack = strtolower($name . ' ' . $html);
        $map = [
            'Skincare'      => ['serum', 'cream', 'moistur', 'sunscreen', 'cleanser', 'toner'],
            'Personal Care' => ['shampoo', 'body wash', 'lotion', 'deodorant', 'soap', 'razor'],
            'Fragrance'     => ['perfume', 'cologne', 'fragrance', 'eau de'],
            'Electronics'   => ['headphone', 'earbud', 'charger', 'cable', 'speaker', 'phone case'],
            'Supplements'   => ['vitamin', 'supplement', 'protein', 'omega', 'collagen'],
            'Toys'          => ['toy', 'lego', 'doll', 'puzzle'],
            'Fitness'       => ['yoga', 'dumbbell', 'resistance band', 'fitness'],
            'Kitchen'       => ['kitchen', 'cookware', 'pan', 'mug', 'bottle'],
            'Baby'          => ['baby', 'diaper', 'pacifier', 'baby food'],
        ];
        foreach ($map as $cat => $terms) {
            foreach ($terms as $t) {
                if (str_contains($haystack, $t)) return $cat;
            }
        }
        return null;
    }

    private function absoluteUrl(string $url, string $base): string
    {
        if (str_starts_with($url, 'http')) return $url;
        if (str_starts_with($url, '//')) return 'https:' . $url;
        $parts = parse_url($base);
        return ($parts['scheme'] ?? 'https') . '://' . ($parts['host'] ?? '') . $url;
    }

    private function cleanText(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }
}
