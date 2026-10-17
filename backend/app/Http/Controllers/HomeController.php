<?php
namespace App\Http\Controllers;

use App\Models\Experience;
use App\Models\Homestay;
use App\Models\LocalProduct;
use App\Models\Room;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $featuredHomestays = Homestay::with(['rooms' => function ($query) {
                $query->where('active', true);
            }])
            ->where('status', 'approved')
            ->orderByDesc('avg_rating')
            ->take(6)
            ->get();

        $featuredProducts = LocalProduct::with('homestays')
            ->where('status', 'published')
            ->latest('id')
            ->take(4)
            ->get();

        $featuredExperiences = Experience::with('homestay')
            ->whereIn('status', ['open', 'full'])
            ->latest('id')
            ->take(3)
            ->get();

        $stats = [
            'homestays_count' => Homestay::where('status', 'approved')->count(),
            'rooms_count' => Room::where('active', true)->count(),
            'products_count' => LocalProduct::where('status', 'published')->count(),
            'experiences_count' => Experience::count(),
            'provinces_count' => Homestay::where('status', 'approved')->distinct('province')->count('province'),
        ];

        $popularDestinations = [
            [
                'name' => 'Lâm Đồng (Đà Lạt)',
                'keyword' => 'Lam Dong',
                'tagline' => 'Thung lũng ngàn hoa & đồi thông mờ sương',
                'badge' => 'Cao nguyên',
                'gradient' => 'linear-gradient(135deg, rgba(18, 96, 90, 0.85), rgba(42, 139, 126, 0.8))',
                'image' => asset('images/banners/dalat-homestay.jpg'),
            ],
            [
                'name' => 'Lào Cai (Sa Pa)',
                'keyword' => 'Lao Cai',
                'tagline' => 'Bản làng mây phủ, ruộng bậc thang & dệt thổ cẩm',
                'badge' => 'Tây Bắc',
                'gradient' => 'linear-gradient(135deg, rgba(32, 92, 106, 0.85), rgba(56, 138, 150, 0.8))',
                'image' => asset('images/banners/hero-banner.jpg'),
            ],
            [
                'name' => 'Quảng Nam (Hội An)',
                'keyword' => 'Quang Nam',
                'tagline' => 'Phố cổ đèn lồng, làng rau Trà Quế & gốm Thanh Hà',
                'badge' => 'Di sản',
                'gradient' => 'linear-gradient(135deg, rgba(197, 106, 44, 0.85), rgba(224, 139, 77, 0.8))',
                'image' => asset('images/banners/hoian-homestay.jpg'),
            ],
            [
                'name' => 'Ninh Bình (Hoa Lư)',
                'keyword' => 'Ninh Binh',
                'tagline' => 'Non nước hữu tình, chèo thuyền Tràng An & hồ sen',
                'badge' => 'Sông nước',
                'gradient' => 'linear-gradient(135deg, rgba(61, 90, 69, 0.85), rgba(91, 130, 100, 0.8))',
                'image' => asset('images/banners/ninhbinh-homestay.jpg'),
            ],
            [
                'name' => 'Hà Giang (Đồng Văn)',
                'keyword' => 'Ha Giang',
                'tagline' => 'Cao nguyên đá hùng vĩ, hoa tam giác mạch',
                'badge' => 'Địa đầu Tổ quốc',
                'gradient' => 'linear-gradient(135deg, rgba(92, 60, 111, 0.85), rgba(130, 87, 155, 0.8))',
                'image' => 'https://images.unsplash.com/photo-1509316975850-ff9c5deb0cd9?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'name' => 'Thừa Thiên Huế',
                'keyword' => 'Thua Thien Hue',
                'tagline' => 'Nét xưa Cố Đô, trà sen Cung đình & làng nón Dạ Lê',
                'badge' => 'Văn hóa',
                'gradient' => 'linear-gradient(135deg, rgba(135, 67, 50, 0.85), rgba(184, 98, 76, 0.8))',
                'image' => 'https://images.unsplash.com/photo-1583417319070-4a69db38a482?auto=format&fit=crop&w=800&q=80',
            ],
        ];

        return view('welcome', compact(
            'featuredHomestays',
            'featuredProducts',
            'featuredExperiences',
            'stats',
            'popularDestinations'
        ));
    }
}
