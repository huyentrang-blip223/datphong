<?php
namespace Tests\Feature;

use App\Mail\BusinessNotificationMail;
use App\Models\Booking;
use App\Models\Homestay;
use App\Models\LocalProduct;
use App\Models\Notification;
use App\Models\ProductOrder;
use App\Models\Room;
use App\Models\RoomAvailability;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class Week9FreezeReadinessTest extends TestCase
{
    use DatabaseTransactions;

    public function test_health_endpoint_reports_app_database_python_and_timestamp(): void
    {
        Http::fake(['*/healthz' => Http::response(['status' => 'ok'], 200)]);

        $this->getJson(route('healthz'))
            ->assertOk()
            ->assertJsonPath('application', 'ok')
            ->assertJsonPath('database', 'ok')
            ->assertJsonPath('python', 'ok')
            ->assertJsonStructure(['timestamp']);
    }

    public function test_security_headers_are_added_to_web_responses(): void
    {
        $response = $this->get(route('home'));

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertStringContainsString("default-src 'self'", $response->headers->get('Content-Security-Policy'));
    }

    public function test_login_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.submit'), [
                'email' => 'missing-week9@dt07.test',
                'password' => 'WrongPass123',
            ])->assertSessionHasErrors('email');
        }

        $this->post(route('login.submit'), [
            'email' => 'missing-week9@dt07.test',
            'password' => 'WrongPass123',
        ])->assertStatus(429);
    }

    public function test_payment_awards_loyalty_notification_and_audit(): void
    {
        [$guest, $booking] = $this->bookingForGuest('w9-pay-guest@dt07.test');

        $this->actingAs($guest)->postJson(route('guest.payments.cash', $booking))->assertCreated();
        $host = $booking->room->homestay->owner;
        $this->actingAs($host)->postJson(route('host.bookings.cash-received', $booking), ['confirmed' => true])->assertOk();

        $this->assertDatabaseHas('loyalty_transactions', [
            'user_id' => $guest->id,
            'booking_id' => $booking->id,
            'reason' => 'cash_payment_booking',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $guest->id,
            'type' => 'payment_paid',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $host->id,
            'action' => 'cash_payment_paid',
            'entity' => 'payments',
        ]);
    }

    public function test_notification_and_loyalty_pages_are_scoped_to_authenticated_user(): void
    {
        [$guest] = $this->bookingForGuest('w9-page-guest@dt07.test');
        Notification::create([
            'user_id' => $guest->id,
            'type' => 'test',
            'title' => 'Scoped',
            'message' => 'Only this user',
        ]);

        $this->actingAs($guest)->get(route('account.notifications.index'))->assertOk()->assertSee('Scoped');
        $this->actingAs($guest)->get(route('account.loyalty.index'))->assertOk()->assertSee('Điểm hiện tại');
    }

    public function test_business_notifications_send_email_when_mail_is_configured(): void
    {
        Mail::fake();
        config([
            'mail.notifications_enabled' => true,
            'mail.mailers.smtp.username' => 'dt07@example.test',
            'mail.mailers.smtp.password' => 'app-password',
        ]);

        [$guest] = $this->bookingForGuest('w9-mail-guest@dt07.test');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $guest->id,
            'type' => 'booking_created',
        ]);
        Mail::assertSent(BusinessNotificationMail::class, 2);

        $booking = Booking::where('guest_id', $guest->id)->latest('id')->firstOrFail();
        $host = $booking->room->homestay->owner;
        $this->actingAs($guest)->postJson(route('guest.bookings.cancel', $booking))->assertOk();

        Mail::assertSent(BusinessNotificationMail::class, fn ($mail) => $mail->hasTo($guest->email)
            && $mail->title === 'Booking đã bị hủy');
        Mail::assertSent(BusinessNotificationMail::class, fn ($mail) => $mail->hasTo($host->email)
            && $mail->title === 'Booking đã bị hủy');
    }

    public function test_host_can_view_only_related_bookings(): void
    {
        [$guest, $booking, $host] = $this->bookingForGuest('w9-host-booking@dt07.test');
        $otherHost = $this->user('w9-other-host@dt07.test', 'host');

        $this->actingAs($host)->get(route('host.bookings.index'))->assertOk()->assertSee($booking->code);
        $this->actingAs($otherHost)->get(route('host.bookings.index'))->assertOk()->assertDontSee($booking->code);
    }

    public function test_product_order_notifications_are_written(): void
    {
        $guest = $this->user('w9-order-guest@dt07.test', 'guest');
        $seller = $this->user('w9-order-seller@dt07.test', 'seller');
        $product = LocalProduct::create([
            'seller_id' => $seller->id,
            'name' => 'W9 Product',
            'origin_place' => 'Da Lat',
            'description' => 'Week9',
            'unit' => 'goi',
            'price' => 100000,
            'stock_qty' => 5,
            'status' => 'published',
        ]);

        $this->actingAs($guest)->postJson(route('guest.orders.store'), [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ])->assertCreated();
        $order = ProductOrder::latest('id')->first();

        $this->assertDatabaseHas('notifications', ['user_id' => $guest->id, 'reference_id' => $order->id]);
        $this->assertDatabaseHas('notifications', ['user_id' => $seller->id, 'reference_id' => $order->id]);
    }

    public function test_role_navigation_only_shows_current_role_actions(): void
    {
        $admin = $this->user('w9-nav-admin@dt07.test', 'admin');
        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard admin')
            ->assertSee('Duyệt homestay')
            ->assertSee('Thanh toán')
            ->assertSee('Audit')
            ->assertDontSee('Tìm homestay')
            ->assertDontSee('Trải nghiệm')
            ->assertDontSee('Thông báo')
            ->assertDontSee('Dashboard host')
            ->assertDontSee('Dashboard seller')
            ->assertDontSee('Booking của tôi');

        $seller = $this->user('w9-nav-seller@dt07.test', 'seller');
        $this->actingAs($seller)->get(route('home'))
            ->assertOk()
            ->assertSee('Dashboard seller')
            ->assertSee('Sản phẩm của tôi')
            ->assertDontSee('Dashboard host')
            ->assertDontSee('Duyệt homestay')
            ->assertDontSee('Booking của tôi');

        $guest = $this->user('w9-nav-guest@dt07.test', 'guest');
        $this->actingAs($guest)->get(route('home'))
            ->assertOk()
            ->assertSee('Booking của tôi')
            ->assertSee('Điểm')
            ->assertDontSee('Dashboard seller')
            ->assertDontSee('Dashboard host')
            ->assertDontSee('Duyệt homestay');
    }

    public function test_admin_can_view_payment_overview_and_audit_logs(): void
    {
        $admin = $this->user('w9-admin-finance@dt07.test', 'admin');
        [$guest, $booking] = $this->bookingForGuest('w9-admin-finance-guest@dt07.test');

        $this->actingAs($guest)->postJson(route('guest.payments.cash', $booking))->assertCreated();
        $this->actingAs($booking->room->homestay->owner)->postJson(route('host.bookings.cash-received', $booking), ['confirmed' => true])->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.payments.index'))
            ->assertOk()
            ->assertSee('Giao dịch thanh toán')
            ->assertSee($booking->code)
            ->assertSee('booking')
            ->assertSee('paid');

        $this->actingAs($admin)
            ->get(route('admin.audit-logs.index', ['entity' => 'payments']))
            ->assertOk()
            ->assertSee('Audit logs')
            ->assertSee('cash_payment_paid');
    }

    private function bookingForGuest(string $email): array
    {
        $guest = $this->user($email, 'guest');
        $host = $this->user($email . '.host', 'host');
        $homestay = Homestay::create([
            'owner_id' => $host->id,
            'name' => 'W9 Homestay ' . uniqid(),
            'slug' => 'w9-homestay-' . uniqid(),
            'province' => 'Lam Dong',
            'address_line' => '1 Week9',
            'status' => 'approved',
        ]);
        $room = Room::create([
            'homestay_id' => $homestay->id,
            'name' => 'W9 Room',
            'room_type' => 'private_room',
            'max_guests' => 2,
            'bed_count' => 1,
            'base_price' => 100000,
            'quantity' => 1,
            'active' => true,
        ]);
        RoomAvailability::create([
            'room_id' => $room->id,
            'stay_date' => '2026-12-28',
            'units_total' => 1,
            'units_held' => 0,
            'units_sold' => 0,
            'status' => 'open',
        ]);

        return [$guest, app(BookingService::class)->createBooking($guest, $room->fresh('homestay'), '2026-12-28', '2026-12-29', 1), $host];
    }

    private function user(string $email, string $role): User
    {
        return User::create([
            'email' => $email,
            'password_hash' => Hash::make('Dt07@2026!'),
            'role' => $role,
            'full_name' => 'Week9 ' . $role,
            'status' => 'active',
        ]);
    }
}
