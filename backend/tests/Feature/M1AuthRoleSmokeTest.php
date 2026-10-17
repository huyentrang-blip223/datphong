<?php
namespace Tests\Feature;

use App\Models\Homestay;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
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

    public function test_register_page_is_available_to_guests(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Đăng ký')
            ->assertSee('Khách du lịch')
            ->assertDontSee('Chủ homestay')
            ->assertDontSee('Người bán sản phẩm');
    }

    public function test_guest_can_register_with_hashed_password_and_is_logged_in(): void
    {
        $response = $this->post(route('register.submit'), [
            'full_name' => 'New Guest',
            'email' => 'new-register-guest@dt07.test',
            'phone' => '0900000000',
            'password' => 'Dt07@2026!',
            'password_confirmation' => 'Dt07@2026!',
        ]);

        $user = User::where('email', 'new-register-guest@dt07.test')->firstOrFail();

        $response->assertRedirect(route('guest.bookings.index'));
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('Dt07@2026!', $user->password_hash));
        $this->assertNotSame('Dt07@2026!', $user->password_hash);
        $this->assertSame('guest', $user->role);
        $this->assertSame('active', $user->status);
    }

    public function test_registration_ignores_any_submitted_privileged_role(): void
    {
        $this->post(route('register.submit'), [
            'full_name' => 'Bad Admin',
            'email' => 'bad-admin-register@dt07.test',
            'role' => 'admin',
            'password' => 'Dt07@2026!',
            'password_confirmation' => 'Dt07@2026!',
        ])->assertRedirect(route('guest.bookings.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'bad-admin-register@dt07.test',
            'role' => 'guest',
        ]);
    }

    public function test_admin_can_upload_homestay_cover_image(): void
    {
        Storage::fake('public');
        $admin = $this->makeUser('admin-upload-smoke@dt07.test', 'admin');
        $host = $this->makeUser('host-upload-smoke@dt07.test', 'host');

        $this->actingAs($admin)->post(route('admin.homestays.store'), [
            'owner_id' => $host->id,
            'name' => 'Homestay Upload Smoke',
            'slug' => 'homestay-upload-smoke',
            'province' => 'Lam Dong',
            'district' => 'Da Lat',
            'address_line' => '1 Upload Street',
            'description' => 'Upload smoke test.',
            'cover_image' => $this->fakePngUpload(),
            'checkin_time' => '14:00',
            'checkout_time' => '12:00',
            'status' => 'pending',
        ])->assertRedirect(route('admin.homestays.index'));

        $homestay = Homestay::where('slug', 'homestay-upload-smoke')->firstOrFail();

        $this->assertNotEmpty($homestay->cover_image_path);
        Storage::disk('public')->assertExists($homestay->cover_image_path);
    }

    public function test_homepage_shows_uploaded_homestay_cover_image(): void
    {
        Storage::fake('public');
        $host = $this->makeUser('host-home-cover-smoke@dt07.test', 'host');
        Storage::disk('public')->put('homestays/home-cover-smoke.png', 'fake image content');
        $homestay = Homestay::create([
            'owner_id' => $host->id,
            'name' => 'Home Cover Smoke',
            'slug' => 'home-cover-smoke',
            'province' => 'Lam Dong',
            'district' => 'Da Lat',
            'address_line' => '1 Home Cover Street',
            'description' => 'Homepage cover image smoke test.',
            'cover_image_path' => 'homestays/home-cover-smoke.png',
            'checkin_time' => '14:00:00',
            'checkout_time' => '12:00:00',
            'status' => 'approved',
            'avg_rating' => 5,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($homestay->name)
            ->assertSee('storage/homestays/home-cover-smoke.png', false)
            ->assertSee('Ảnh homestay ' . $homestay->name);
    }

    private function fakePngUpload(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'dt07-cover-') . '.png';
        file_put_contents($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII='));

        return new UploadedFile($path, 'cover.png', 'image/png', null, true);
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
