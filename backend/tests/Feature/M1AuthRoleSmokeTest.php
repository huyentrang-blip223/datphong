<?php
namespace Tests\Feature;

use App\Models\Homestay;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class M1AuthRoleSmokeTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_is_redirected_from_admin(): void
    {
        $this->get('/quan-tri/bang-dieu-khien')->assertRedirect('/dang-nhap');
    }

    public function test_host_gets_403_on_admin_route(): void
    {
        $user = $this->makeUser('host-smoke@dt07.test', 'host');

        $this->actingAs($user)->get('/quan-tri/bang-dieu-khien')->assertForbidden();
    }

    public function test_admin_can_open_homestay_crud(): void
    {
        $user = $this->makeUser('admin-smoke@dt07.test', 'admin');

        $this->actingAs($user)->get('/quan-tri/homestays')->assertOk();
    }

    public function test_seller_gets_403_on_host_dashboard(): void
    {
        $user = $this->makeUser('seller-smoke@dt07.test', 'seller');

        $this->actingAs($user)->get('/chu-homestay/bang-dieu-khien')->assertForbidden();
    }

    public function test_host_cannot_edit_another_hosts_homestay(): void
    {
        $hostA = $this->makeUser('host-a-smoke@dt07.test', 'host');
        $hostB = $this->makeUser('host-b-smoke@dt07.test', 'host');
        $homestay = $this->makeHomestay($hostB, 'other-owner-smoke');

        $this->actingAs($hostA)
            ->get(route('host.homestays.edit', $homestay))
            ->assertForbidden();
    }

    public function test_stale_logout_token_redirects_to_login_instead_of_419(): void
    {
        $user = $this->makeUser('logout-stale-smoke@dt07.test', 'guest');

        $this->actingAs($user)
            ->withSession(['_token' => 'fresh-session-token'])
            ->post('/dang-xuat', ['_token' => 'stale-form-token'])
            ->assertRedirect('/dang-nhap');
    }

    private function makeUser(string $email, string $role): User
    {
        return User::create([
            'email' => $email,
            'password_hash' => Hash::make('Dt07@2026!'),
            'role' => $role,
            'full_name' => ucfirst($role) . ' Smoke',
            'status' => 'active',
        ]);
    }

    private function makeHomestay(User $owner, string $slug): Homestay
    {
        return Homestay::create([
            'owner_id' => $owner->id,
            'name' => 'Homestay Smoke',
            'slug' => $slug,
            'province' => 'Lam Dong',
            'district' => 'Da Lat',
            'address_line' => '1 Smoke Street',
            'description' => 'Smoke test homestay.',
            'checkin_time' => '14:00:00',
            'checkout_time' => '12:00:00',
            'status' => 'pending',
            'avg_rating' => 0,
        ]);
    }
}
