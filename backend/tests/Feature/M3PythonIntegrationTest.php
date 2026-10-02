<?php
namespace Tests\Feature;

use App\Models\Homestay;
use App\Models\Experience;
use App\Models\LocalProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class M3PythonIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        config([
            'services.python_data.url' => 'http://python.test',
            'services.python_data.token' => 'test-token',
            'services.python_data.timeout' => 3,
        ]);
    }

    public function test_python_service_success_recommendations_are_rendered(): void
    {
        [$product, $related] = $this->makeProducts();

        Http::fake([
            'python.test/recommend/local-products/*' => Http::response([
                'source' => 'python',
                'items' => [[
                    'product_id' => $related->id,
                    'name' => $related->name,
                    'origin_place' => $related->origin_place,
                    'price' => (float) $related->price,
                    'score' => 0.77,
                ]],
            ], 200),
        ]);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Python service')
            ->assertSee($related->name);
    }

    public function test_python_service_error_uses_fallback_and_page_does_not_500(): void
    {
        [$product, $related] = $this->makeProducts();

        Http::fake([
            'python.test/recommend/local-products/*' => Http::response(['message' => 'down'], 500),
        ]);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Laravel fallback')
            ->assertSee($related->name);
    }

    public function test_recommendation_cache_key_is_created(): void
    {
        [$product] = $this->makeProducts();

        Http::fake([
            'python.test/recommend/local-products/*' => Http::response(['items' => []], 200),
        ]);

        $this->get(route('products.show', $product))->assertOk();

        $this->assertTrue(Cache::has("py:recommend:local-product:{$product->id}:6"));
    }

    public function test_admin_dashboard_renders_when_python_down(): void
    {
        $admin = $this->makeUser('m3-admin@dt07.test', 'admin');

        Http::fake([
            'python.test/*' => Http::response(['message' => 'down'], 503),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard quản trị')
            ->assertSee('Fallback/empty state');
    }

    public function test_guest_can_book_available_experience(): void
    {
        $guest = $this->makeUser('m3-experience-guest@dt07.test', 'guest');
        $experience = $this->makeExperience();

        $this->actingAs($guest)
            ->postJson(route('experiences.book', $experience), ['people_count' => 2])
            ->assertCreated()
            ->assertJsonPath('data.status', 'confirmed');

        $this->assertSame(3, (int) $experience->fresh()->booked_count);
    }

    public function test_experience_capacity_conflict_returns_409(): void
    {
        $guest = $this->makeUser('m3-experience-conflict@dt07.test', 'guest');
        $experience = $this->makeExperience(['capacity' => 2, 'booked_count' => 1]);

        $this->actingAs($guest)
            ->postJson(route('experiences.book', $experience), ['people_count' => 2])
            ->assertStatus(409);
    }

    private function makeProducts(): array
    {
        $seller = $this->makeUser('m3-seller@dt07.test', 'seller');
        $host = $this->makeUser('m3-host@dt07.test', 'host');
        $homestay = Homestay::create([
            'owner_id' => $host->id,
            'name' => 'M3 Homestay',
            'slug' => 'm3-homestay-' . uniqid(),
            'province' => 'Lam Dong',
            'district' => 'Da Lat',
            'address_line' => '1 M3 Street',
            'description' => 'M3 homestay.',
            'checkin_time' => '14:00:00',
            'checkout_time' => '12:00:00',
            'status' => 'approved',
            'avg_rating' => 4,
        ]);

        $product = LocalProduct::create([
            'seller_id' => $seller->id,
            'name' => 'M3 Tea',
            'origin_place' => 'Lam Dong',
            'description' => 'Local tea',
            'unit' => 'goi',
            'price' => 100000,
            'stock_qty' => 10,
            'status' => 'published',
        ]);
        $related = LocalProduct::create([
            'seller_id' => $seller->id,
            'name' => 'M3 Coffee',
            'origin_place' => 'Lam Dong',
            'description' => 'Local coffee',
            'unit' => 'goi',
            'price' => 120000,
            'stock_qty' => 10,
            'status' => 'published',
        ]);

        $homestay->localProducts()->attach([$product->id, $related->id]);

        return [$product, $related];
    }

    private function makeExperience(array $overrides = []): Experience
    {
        $host = $this->makeUser('m3-experience-host-' . uniqid() . '@dt07.test', 'host');
        $homestay = Homestay::create([
            'owner_id' => $host->id,
            'name' => 'M3 Experience Homestay',
            'slug' => 'm3-experience-homestay-' . uniqid(),
            'province' => 'Lam Dong',
            'district' => 'Da Lat',
            'address_line' => '2 M3 Street',
            'description' => 'M3 experience homestay.',
            'checkin_time' => '14:00:00',
            'checkout_time' => '12:00:00',
            'status' => 'approved',
            'avg_rating' => 4,
        ]);

        return Experience::create(array_merge([
            'homestay_id' => $homestay->id,
            'title' => 'M3 Tea Workshop',
            'description' => 'Local cultural workshop.',
            'start_at' => '2026-10-20 09:00:00',
            'duration_minutes' => 120,
            'capacity' => 6,
            'booked_count' => 1,
            'price' => 150000,
            'status' => 'open',
        ], $overrides));
    }

    private function makeUser(string $email, string $role): User
    {
        return User::create([
            'email' => $email,
            'password_hash' => Hash::make('Dt07@2026!'),
            'role' => $role,
            'full_name' => ucfirst($role) . ' M3',
            'status' => 'active',
        ]);
    }
}
