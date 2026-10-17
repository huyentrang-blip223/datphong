<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSellerProductRequest;
use App\Models\LocalProduct;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = LocalProduct::with('seller')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $product = new LocalProduct();
        $sellers = User::where('role', 'seller')->orderBy('full_name')->orderBy('email')->get();

        return view('admin.products.create', compact('product', 'sellers'));
    }

    public function store(StoreSellerProductRequest $request)
    {
        $data = $request->validated();
        unset($data['image']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        LocalProduct::create($data);

        return redirect()->route('admin.products.index')->with('status', 'Đã tạo sản phẩm.');
    }

    public function edit(LocalProduct $product)
    {
        $sellers = User::where('role', 'seller')->orderBy('full_name')->orderBy('email')->get();

        return view('admin.products.edit', compact('product', 'sellers'));
    }

    public function update(StoreSellerProductRequest $request, LocalProduct $product)
    {
        $data = $request->validated();
        unset($data['image']);

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }

            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return redirect()->route('admin.products.index')->with('status', 'Đã cập nhật sản phẩm.');
    }

    public function destroy(LocalProduct $product)
    {
        try {
            $imagePath = $product->image_path;
            $product->delete();

            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
        } catch (QueryException $e) {
            return back()->withErrors('Không thể xóa sản phẩm đã có đơn hàng hoặc liên kết dữ liệu. Hãy chuyển trạng thái sang tạm ẩn.');
        }

        return redirect()->route('admin.products.index')->with('status', 'Đã xóa sản phẩm.');
    }
}
