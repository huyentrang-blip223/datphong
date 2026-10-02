<?php
namespace App\Services;

use App\Models\LocalProduct;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class PythonDataService
{
    public function localProductRecommendations(LocalProduct $product, int $limit = 6): array
    {
        $limit = max(1, min($limit, 12));
        $cacheKey = "py:recommend:local-product:{$product->id}:{$limit}";

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($product, $limit) {
            try {
                $response = $this->client()
                    ->retry(2, 100)
                    ->get("/recommend/local-products/{$product->id}", ['k' => $limit]);

                if ($response->successful()) {
                    $data = $response->json();
                    return [
                        'source' => 'python',
                        'items' => $data['items'] ?? [],
                        'message' => $data['message'] ?? null,
                    ];
                }

                Log::warning('Python recommendation service returned non-2xx.', [
                    'status' => $response->status(),
                    'product_id' => $product->id,
                ]);
            } catch (Throwable $exception) {
                Log::warning('Python recommendation service unavailable.', [
                    'product_id' => $product->id,
                    'error' => $exception->getMessage(),
                ]);
            }

            return [
                'source' => 'fallback',
                'items' => $this->fallbackRecommendations($product, $limit),
                'message' => 'Đang dùng gợi ý dự phòng từ Laravel.',
            ];
        });
    }

    public function seasonality(): array
    {
        return Cache::remember('py:analytics:seasonality', now()->addMinutes(30), function () {
            return $this->analyticsRequest('/analytics/seasonality', 'seasonality');
        });
    }

    public function topHomestays(int $limit = 10): array
    {
        $limit = max(1, min($limit, 30));

        return Cache::remember("py:analytics:top-homestays:{$limit}", now()->addMinutes(30), function () use ($limit) {
            return $this->analyticsRequest('/analytics/top-homestays', 'top_homestays', ['limit' => $limit]);
        });
    }

    private function analyticsRequest(string $path, string $key, array $query = []): array
    {
        try {
            $response = $this->client()->retry(2, 100)->get($path, $query);

            if ($response->successful()) {
                return ['source' => 'python', 'items' => $response->json($key) ?? []];
            }

            Log::warning('Python analytics service returned non-2xx.', [
                'path' => $path,
                'status' => $response->status(),
            ]);
        } catch (Throwable $exception) {
            Log::warning('Python analytics service unavailable.', [
                'path' => $path,
                'error' => $exception->getMessage(),
            ]);
        }

        return ['source' => 'fallback', 'items' => []];
    }

    private function client()
    {
        $baseUrl = rtrim((string) config('services.python_data.url'), '/');
        $token = (string) config('services.python_data.token');
        $timeout = (float) config('services.python_data.timeout', 3);

        $client = Http::baseUrl($baseUrl)->timeout($timeout)->acceptJson();

        if ($token !== '') {
            $client = $client->withHeaders(['X-Service-Token' => $token]);
        }

        return $client;
    }

    private function fallbackRecommendations(LocalProduct $product, int $limit): array
    {
        $homestayIds = $product->homestays()->pluck('homestays.id')->all();
        $minPrice = max(0, (float) $product->price * 0.5);
        $maxPrice = (float) $product->price * 1.5;

        return LocalProduct::query()
            ->select(['local_products.id as product_id', 'local_products.name', 'local_products.price', 'local_products.origin_place'])
            ->where('local_products.status', 'published')
            ->where('local_products.id', '<>', $product->id)
            ->when($homestayIds, fn($q) => $q->whereHas('homestays', fn($h) => $h->whereIn('homestays.id', $homestayIds)))
            ->whereBetween('local_products.price', [$minPrice, $maxPrice])
            ->orderBy('local_products.price')
            ->limit($limit)
            ->get()
            ->map(fn($item) => [
                'product_id' => (int) $item->product_id,
                'name' => $item->name,
                'price' => (float) $item->price,
                'origin_place' => $item->origin_place,
                'score' => null,
            ])
            ->all();
    }
}
