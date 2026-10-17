<?php

namespace Tests\Feature;

use App\Models\LocalProduct;
use App\Models\ProductOrder;
use App\Models\User;
use App\Notifications\ResetAccountPassword;
use App\Services\LoyaltyService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Tests\TestCase;

class AccountCartTest extends TestCase
{
    use DatabaseTransactions;

    public function test_profile_requires_password_and_ignores_privileged_fields(): void
    {
        $user = $this->user('guest');
        $data = ['full_name' => 'Updated guest', 'email' => 'updated-'.uniqid().'@example.test', 'phone' => '0901234567', 'current_password' => 'wrong-password', 'role' => 'admin', 'status' => 'locked'];
        $this->actingAs($user)->putJson(route('account.profile.update'), $data)->assertUnprocessable();
        $data['current_password'] = 'Dt07@2026!';
        $this->put(route('account.profile.update'), $data)->assertRedirect();
        $this->assertSame('Updated guest', $user->fresh()->full_name);
        $this->assertSame('guest', $user->fresh()->role);
        $this->assertSame('active', $user->fresh()->status);
    }

    public function test_password_change_hashes_password_and_revokes_old_session_signature(): void
    {
        $user = $this->user('guest');
        $old = hash('sha256', $user->password_hash);
        $this->actingAs($user)->put(route('account.password.update'), ['current_password' => 'Dt07@2026!', 'password' => 'Changed2026!', 'password_confirmation' => 'Changed2026!'])->assertRedirect();
        $this->assertTrue(Hash::check('Changed2026!', $user->fresh()->password_hash));
        $this->get(route('account.profile.edit'))->assertOk();
        $this->withSession(['account_fingerprint' => $old, 'account_fingerprint_user' => $user->id])->getJson(route('account.profile.edit'))->assertUnauthorized();
    }

    public function test_recovery_is_uniform_and_token_is_hashed_single_use(): void
    {
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
        Notification::fake();
        $user = $this->user('guest');
        $a = $this->post(route('password.email'), ['email' => $user->email])->assertRedirect()->getSession()->get('status');
        $b = $this->post(route('password.email'), ['email' => 'absent@example.test'])->assertRedirect()->getSession()->get('status');
        $this->assertSame($a, $b);
        $sent = Notification::sent($user, ResetAccountPassword::class)->first();
        $this->assertNotNull($sent);
        $stored = DB::table('password_resets')->where('email', $user->email)->value('token');
        $this->assertNotSame($sent->token, $stored);
        $this->assertTrue(Hash::check($sent->token, $stored));
        $data = ['email' => $user->email, 'token' => $sent->token, 'password' => 'Reset2026!', 'password_confirmation' => 'Reset2026!'];
        $this->post(route('password.update'), $data)->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('Reset2026!', $user->fresh()->password_hash));
        $this->post(route('password.update'), $data)->assertSessionHasErrors('email');
    }

    public function test_expired_and_locked_account_reset_links_are_rejected(): void
    {
        $user = $this->user('guest');
        $token = Password::createToken($user);
        DB::table('password_resets')->where('email', $user->email)->update(['created_at' => now()->subHours(2)]);
        $data = ['email' => $user->email, 'token' => $token, 'password' => 'Reset2026!', 'password_confirmation' => 'Reset2026!'];
        $this->post(route('password.update'), $data)->assertSessionHasErrors('email');
        $user->update(['status' => 'locked']);
        $data['token'] = Password::createToken($user);
        $this->post(route('password.update'), $data)->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('Dt07@2026!', $user->fresh()->password_hash));
    }

    public function test_reset_links_use_configured_origin_not_host_header(): void
    {
        $user = $this->user('guest');
        config(['app.url' => 'https://trusted.example.test']);
        $mail = (new ResetAccountPassword('secret-token'))->toMail($user);
        $this->assertStringStartsWith('https://trusted.example.test/', $mail->actionUrl);
    }

    public function test_admin_can_create_host_and_lock_an_existing_session_immediately(): void
    {
        $admin = $this->user('admin');
        $data = ['email' => uniqid().'@example.test', 'full_name' => 'Managed Host', 'role' => 'host', 'status' => 'active', 'password' => 'Managed2026!', 'password_confirmation' => 'Managed2026!'];
        $response = $this->actingAs($admin)->postJson(route('admin.users.store'), $data)->assertCreated();
        $target = User::findOrFail($response->json('data.id'));
        $this->assertTrue(Hash::check('Managed2026!', $target->password_hash));
        $this->actingAs($target)->get(route('host.dashboard'))->assertOk();
        $target->update(['status' => 'locked']);
        $this->getJson(route('host.dashboard'))->assertUnauthorized();
        $this->actingAs($this->user('guest'))->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_admin_cannot_self_demote_or_change_resource_owner_role(): void
    {
        $admin = $this->user('admin');
        $data = ['email' => $admin->email, 'full_name' => $admin->full_name, 'role' => 'guest', 'status' => 'active'];
        $this->actingAs($admin)->putJson(route('admin.users.update', $admin), $data)->assertUnprocessable();
        $seller = $this->user('seller');
        $this->product($seller);
        $data = ['email' => $seller->email, 'full_name' => $seller->full_name, 'role' => 'host', 'status' => 'active'];
        $this->putJson(route('admin.users.update', $seller), $data)->assertUnprocessable();
        $empty = $this->user('guest');
        $data['email'] = $empty->email;
        $this->putJson(route('admin.users.update', $empty), $data)->assertOk()->assertJsonPath('data.role', 'host');
    }

    public function test_cart_splits_sellers_applies_points_and_replays_without_duplicate_orders(): void
    {
        $guest = $this->user('guest');
        $a = $this->product($this->user('seller'));
        $b = $this->product($this->user('seller'));
        app(LoyaltyService::class)->award($guest, 100, 'test_seed');
        $key = (string) Str::uuid();
        $this->actingAs($guest)->withSession(['cart' => ['items' => [$a->id => 2, $b->id => 1], 'key' => $key]]);
        $this->get(route('guest.cart.index'))->assertOk()->assertSee($a->name)->assertSee($b->name);
        $data = ['checkout_key' => $key, 'points' => 80, 'total_amount' => 1];
        $response = $this->postJson(route('guest.cart.checkout'), $data)->assertCreated()->assertJsonCount(2, 'data');
        $this->assertSame(220000.0, collect($response->json('data'))->sum(fn ($o) => (float) $o['total_amount']));
        $this->assertSame(20, app(LoyaltyService::class)->balance($guest->id));
        $this->assertSame(8, (int) $a->fresh()->stock_qty);
        $this->postJson(route('guest.cart.checkout'), $data)->assertCreated()->assertJsonCount(2, 'data');
        $this->assertSame(2, ProductOrder::where('buyer_id', $guest->id)->count());
        $this->assertSame(8, (int) $a->fresh()->stock_qty);
    }

    public function test_cart_rolls_back_every_seller_if_one_product_is_out_of_stock(): void
    {
        $guest = $this->user('guest');
        $a = $this->product($this->user('seller'));
        $b = $this->product($this->user('seller'));
        $b->update(['stock_qty' => 0]);
        $key = (string) Str::uuid();
        $this->actingAs($guest)->withSession(['cart' => ['items' => [$a->id => 1, $b->id => 1], 'key' => $key]])->postJson(route('guest.cart.checkout'), ['checkout_key' => $key])->assertConflict();
        $this->assertSame(10, (int) $a->fresh()->stock_qty);
        $this->assertSame(0, ProductOrder::where('buyer_id', $guest->id)->count());
        $this->assertSame(0, DB::table('cart_checkouts')->where('checkout_key', $key)->count());
    }

    public function test_redemption_enforces_balance_and_per_order_cap_then_returns_points_on_cancel(): void
    {
        $guest = $this->user('guest');
        $p = $this->product($this->user('seller'));
        app(LoyaltyService::class)->award($guest, 100, 'test_seed');
        $this->actingAs($guest);
        $data = ['items' => [['product_id' => $p->id, 'quantity' => 1]], 'points' => 51];
        $this->postJson(route('guest.orders.store'), $data)->assertUnprocessable();
        $data['points'] = 50;
        $response = $this->postJson(route('guest.orders.store'), $data)->assertCreated()->assertJsonPath('data.total_amount', '50000.00');
        $order = ProductOrder::findOrFail($response->json('data.id'));
        $this->assertSame(50, app(LoyaltyService::class)->balance($guest->id));
        $this->postJson(route('guest.orders.status', $order), ['status' => 'cancelled'])->assertOk();
        $this->assertSame(100, app(LoyaltyService::class)->balance($guest->id));
        $this->postJson(route('guest.orders.status', $order), ['status' => 'cancelled'])->assertUnprocessable();
        $this->assertSame(100, app(LoyaltyService::class)->balance($guest->id));
    }

    public function test_cart_add_update_remove_and_role_authorization(): void
    {
        $guest = $this->user('guest');
        $p = $this->product($this->user('seller'));
        $this->actingAs($guest)->post(route('guest.cart.store'), ['product_id' => $p->id, 'quantity' => 2])->assertRedirect(route('guest.cart.index'));
        $this->post(route('guest.cart.store'), ['product_id' => $p->id, 'quantity' => 2])->assertSessionHas('cart.items.'.$p->id, 4);
        $this->put(route('guest.cart.update'), ['product_id' => $p->id, 'quantity' => 0])->assertRedirect();
        $this->get(route('guest.cart.index'))->assertOk()->assertSee('Giỏ hàng đang trống.');
        $this->actingAs($this->user('host'))->get(route('guest.cart.index'))->assertForbidden();
    }

    public function test_mail_api_transport_supports_reset_email_without_smtp(): void
    {
        \Illuminate\Support\Facades\Http::fake(['api.resend.com/*' => \Illuminate\Support\Facades\Http::response(['id' => 'mail-test'], 200)]);
        config(['mail.default' => 'resend', 'services.resend.key' => 'test-mail-key', 'mail.from.address' => 'sender@example.test']);
        $user = $this->user('guest');
        $this->post(route('password.email'), ['email' => $user->email])->assertRedirect();
        \Illuminate\Support\Facades\Http::assertSent(fn ($request) => $request->url() === 'https://api.resend.com/emails' && $request['to'] === [$user->email] && str_contains($request['html'], 'dat-lai-mat-khau'));
    }

    public function test_insufficient_points_roll_back_stock_and_order(): void
    {
        $guest = $this->user('guest');
        $product = $this->product($this->user('seller'));
        $this->actingAs($guest)->postJson(route('guest.orders.store'), ['items' => [['product_id' => $product->id, 'quantity' => 1]], 'points' => 20])->assertUnprocessable();
        $this->assertSame(10, (int) $product->fresh()->stock_qty);
        $this->assertSame(0, ProductOrder::where('buyer_id', $guest->id)->count());
    }

    private function user(string $role): User
    {
        return User::create(['email' => uniqid('ac-').'@example.test', 'password_hash' => Hash::make('Dt07@2026!'), 'full_name' => 'Account '.$role, 'role' => $role, 'status' => 'active']);
    }

    private function product(User $seller): LocalProduct
    {
        return LocalProduct::create(['seller_id' => $seller->id, 'name' => uniqid('Cart product '), 'origin_place' => 'Da Lat', 'unit' => 'goi', 'price' => 100000, 'stock_qty' => 10, 'status' => 'published']);
    }
}
