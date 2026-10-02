<?php
namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\LocalProduct;
use App\Services\PythonDataService;
use Illuminate\Http\Request;

class LocalProductController extends Controller
{
    public function index(Request $request)
    {
        $products = LocalProduct::with('homestays')
            ->where('status', 'published')
            ->when($request->query('q'), function ($query, $q) {
                $query->where(function ($search) use ($q) {
                    $search->where('name', 'like', "%{$q}%")
                        ->orWhere('origin_place', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->when($request->query('origin_place'), fn($query, $origin) => $query->where('origin_place', 'like', "%{$origin}%"))
            ->when($request->query('min_price'), fn($query, $price) => $query->where('price', '>=', $price))
            ->when($request->query('max_price'), fn($query, $price) => $query->where('price', '<=', $price))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('products.index', compact('products'));
    }

    public function show(LocalProduct $product, PythonDataService $pythonDataService)
    {
        abort_unless($product->status === 'published', 404);

        $product->load('homestays');
        $recommendations = $pythonDataService->localProductRecommendations($product, 6);

        return view('products.show', compact('product', 'recommendations'));
    }
}
