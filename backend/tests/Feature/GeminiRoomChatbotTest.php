<?php

namespace Tests\Feature;

use App\Models\Amenity;
use App\Models\Homestay;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeminiRoomChatbotTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.gemini.enabled' => true,
            'services.gemini.api_key' => 'TEST_GEMINI_KEY',
            'services.gemini.model' => 'gemini-3.5-flash',
            'services.gemini.timeout' => 2,
        ]);
    }

    public function test_chatbot_uses_gemini_to_rank_real_database_rooms_only(): void
    {
        [$first, $second] = $this->rooms();
        $geminiText = json_encode([
            'message' => 'Phòng thứ hai hợp sở thích thiên nhiên nhất.',
            'recommendations' => [
                ['room_id' => 999999, 'reason' => 'Phòng không tồn tại'],
                ['room_id' => $second->id, 'reason' => 'Có không gian yên tĩnh và phù hợp 4 khách.'],
            ],
        ], JSON_UNESCAPED_UNICODE);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => $geminiText]]]]],
        ], 200)]);

        $this->postJson(route('chatbot.rooms'), [
            'guests' => 4,
            'location' => 'Lâm Đồng',
            'max_price' => 900000,
            'message' => 'Muốn nơi yên tĩnh, gần thiên nhiên và phù hợp gia đình.',
        ])->assertOk()
            ->assertJsonPath('data.source', 'gemini')
            ->assertJsonPath('data.recommendations.0.id', $second->id)
            ->assertJsonCount(1, 'data.recommendations');

        Http::assertSent(fn ($request) => $request->hasHeader('x-goog-api-key', 'TEST_GEMINI_KEY')
            && str_contains($request->url(), 'gemini-3.5-flash:generateContent')
            && ! str_contains($request->url(), 'TEST_GEMINI_KEY'));
        $this->assertNotSame($first->id, $second->id);
    }

    public function test_chatbot_falls_back_to_database_when_gemini_is_unavailable(): void
    {
        $this->rooms();
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'quota']], 429)]);

        $response = $this->postJson(route('chatbot.rooms'), [
            'guests' => 2,
            'message' => 'Phòng riêng có giá hợp lý.',
        ])->assertOk()->assertJsonPath('data.source', 'fallback');

        $this->assertNotEmpty($response->json('data.recommendations'));
        $this->assertLessThanOrEqual(3, count($response->json('data.recommendations')));
    }

    public function test_chatbot_validates_guest_count_and_date_pair(): void
    {
        $this->postJson(route('chatbot.rooms'), [
            'guests' => 0,
            'checkin' => now()->addDays(2)->toDateString(),
            'message' => 'Tìm phòng cho gia đình.',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['guests', 'checkout']);
    }

    public function test_chatbot_widget_is_rendered_without_exposing_api_key(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Gợi ý phòng AI')
            ->assertSee('js/room-chatbot.js')
            ->assertDontSee('TEST_GEMINI_KEY');
    }

    private function rooms(): array
    {
        $host = User::create([
            'email' => uniqid('gemini-host-').'@dt07.test',
            'password_hash' => Hash::make('Dt07@2026!'),
            'role' => 'host',
            'full_name' => 'Gemini Host',
            'status' => 'active',
        ]);
        $homestay = Homestay::create([
            'owner_id' => $host->id,
            'name' => 'Gemini Garden Homestay',
            'slug' => 'gemini-garden-'.uniqid(),
            'province' => 'Lâm Đồng',
            'district' => 'Đà Lạt',
            'address_line' => '1 Đường Hoa',
            'description' => 'Không gian yên tĩnh gần rừng thông, phù hợp gia đình.',
            'status' => 'approved',
            'avg_rating' => 4.8,
        ]);
        $first = Room::create([
            'homestay_id' => $homestay->id,
            'name' => 'Phòng Riêng Vườn Hoa',
            'room_type' => 'private_room',
            'max_guests' => 4,
            'bed_count' => 2,
            'base_price' => 550000,
            'quantity' => 1,
            'active' => true,
        ]);
        $second = Room::create([
            'homestay_id' => $homestay->id,
            'name' => 'Bungalow Rừng Thông',
            'room_type' => 'bungalow',
            'max_guests' => 5,
            'bed_count' => 2,
            'base_price' => 800000,
            'quantity' => 1,
            'active' => true,
        ]);
        $amenity = Amenity::firstOrCreate(
            ['name' => 'Ban công nhìn rừng'],
            ['icon_key' => 'forest', 'category' => 'room']
        );
        $second->amenities()->attach($amenity->id, ['is_highlighted' => true]);

        return [$first, $second];
    }
}
