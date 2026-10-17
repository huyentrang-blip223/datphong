<?php
namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSellerProductRequest;
use App\Models\LocalProduct;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = LocalProduct::where('seller_id', request()->user()->id)->orderByDesc('id')->paginate(10);

        return view('seller.products.index', compact('products'));
    }

    public function create()
    {
        return view('seller.products.create', ['product' => new LocalProduct()]);
    }

    public function store(StoreSellerProductRequest $request)
    {
        $data = $request->validated();
        unset($data['image'], $data['seller_id']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        LocalProduct::create($data + ['seller_id' => $request->user()->id]);

        return redirect()->route('seller.products.index')->with('status', 'Đã tạo sản phẩm.');
    }

    public function edit(LocalProduct $product)
    {
        abort_unless((int) $product->seller_id === (int) request()->user()->id, 403);

        return view('seller.products.edit', compact('product'));
    }

    public function update(StoreSellerProductRequest $request, LocalProduct $product)
    {
        abort_unless((int) $product->seller_id === (int) $request->user()->id, 403);
        $data = $request->validated();
        unset($data['image'], $data['seller_id']);

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }

            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return redirect()->route('seller.products.index')->with('status', 'Đã cập nhật sản phẩm.');
    }
}
