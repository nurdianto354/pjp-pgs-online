<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfilControllerSecurityTest extends TestCase
{
    use DatabaseTransactions;

    protected function createUser(array $attributes = [])
    {
        $unique = uniqid();
        return User::create(array_merge([
            'username' => 'user_' . $unique,
            'nama'     => 'User ' . $unique,
            'email'    => 'user_' . $unique . '@example.test',
            'password' => Hash::make('secret_default_123'),
            'no_telp'  => '081234567890',
            'status'   => true,
        ], $attributes));
    }

    /**
     * Test 7: Request tanpa autentikasi tidak dapat mengakses endpoint yang dilindungi.
     */
    public function test_unauthenticated_user_cannot_access_profile_routes()
    {
        $this->get(route('profil.index'))->assertRedirect(route('login'));
        $this->get(route('profil.update', ['id' => 1, 'view' => 'data']))->assertRedirect(route('login'));
        $this->put(route('profil.store'))->assertRedirect(route('login'));
    }

    /**
     * Test 1: Pengguna dapat membuka dan memperbarui profilnya sendiri.
     */
    public function test_user_can_view_and_update_own_profile()
    {
        $user = $this->createUser([
            'nama'    => 'Original Name',
            'email'   => 'original_test@example.test',
            'no_telp' => '081234567890',
        ]);

        $this->actingAs($user)
            ->get(route('profil.index'))
            ->assertStatus(200)
            ->assertSee('Original Name');

        $response = $this->actingAs($user)
            ->put(route('profil.store'), [
                'view'     => 'data',
                'username' => $user->username,
                'nama'     => 'Updated Name',
                'email'    => 'original_test@example.test',
                'no_telp'  => '0899999999',
            ]);

        $response->assertRedirect(route('profil.index'));
        $user->refresh();
        $this->assertEquals('Updated Name', $user->nama);
        $this->assertEquals('0899999999', $user->no_telp);
    }

    /**
     * Test 2: Manipulasi ID URL tidak menampilkan data profil pengguna lain.
     */
    public function test_url_id_manipulation_does_not_expose_other_user_data()
    {
        $victim = $this->createUser([
            'nama'  => 'Victim Secret Name',
            'email' => 'victim_secret@example.test',
        ]);

        $attacker = $this->createUser([
            'nama'  => 'Attacker Real Name',
            'email' => 'attacker_real@example.test',
        ]);

        // Attacker accesses /profil/update/{victim->id}/data
        $response = $this->actingAs($attacker)
            ->get(route('profil.update', ['id' => $victim->id, 'view' => 'data']));

        $response->assertStatus(200);
        // The view must contain attacker's data, NOT victim's data
        $response->assertSee($attacker->nama);
        $response->assertDontSee($victim->nama);
    }

    /**
     * Test 3: Manipulasi id pada POST tidak mengubah data pengguna lain (IDOR Protection).
     */
    public function test_post_id_tampering_does_not_modify_other_user()
    {
        $victim = $this->createUser([
            'nama'  => 'Victim Untouched',
            'email' => 'victim_untouched@example.test',
        ]);

        $attacker = $this->createUser([
            'nama'  => 'Attacker Attacking',
            'email' => 'attacker_safe@example.test',
        ]);

        // Attacker submits POST with id=victim->id
        $this->actingAs($attacker)
            ->put(route('profil.store'), [
                'id'       => $victim->id,
                'view'     => 'data',
                'username' => $attacker->username,
                'nama'     => 'Hacked Attacker Name',
                'email'    => 'attacker_safe@example.test',
                'no_telp'  => '08000000',
            ]);

        $victim->refresh();
        $attacker->refresh();

        // Victim remains untouched
        $this->assertEquals('Victim Untouched', $victim->nama);
        $this->assertEquals('victim_untouched@example.test', $victim->email);

        // Attacker's own profile is updated
        $this->assertEquals('Hacked Attacker Name', $attacker->nama);
    }

    /**
     * Test 4: Password lama yang salah tidak mengubah password siapa pun.
     */
    public function test_wrong_current_password_does_not_change_password()
    {
        $user = $this->createUser([
            'password' => Hash::make('correct_password_123'),
        ]);

        $response = $this->actingAs($user)
            ->from(route('profil.index'))
            ->put(route('profil.store'), [
                'view'                      => 'password',
                'current_password'          => 'wrong_password',
                'new_password'              => 'new_secret_password',
                'new_password_confirmation' => 'new_secret_password',
            ]);

        $response->assertRedirect(route('profil.index'));
        $response->assertSessionHas('error', 'Gagal password lama salah.');

        $user->refresh();
        $this->assertTrue(Hash::check('correct_password_123', $user->password));
        $this->assertFalse(Hash::check('new_secret_password', $user->password));
    }

    /**
     * Test 5: Password lama yang benar memungkinkan perubahan password akun aktif sesuai aturan existing.
     */
    public function test_correct_current_password_updates_own_password()
    {
        $user = $this->createUser([
            'password' => Hash::make('old_password_123'),
        ]);

        $response = $this->actingAs($user)
            ->put(route('profil.store'), [
                'view'                      => 'password',
                'current_password'          => 'old_password_123',
                'new_password'              => 'new_password_123',
                'new_password_confirmation' => 'new_password_123',
            ]);

        $response->assertRedirect(route('profil.index'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('new_password_123', $user->password));
    }

    /**
     * Test 6: Email milik pengguna aktif dapat dipertahankan, tetapi email duplikat milik pengguna lain ditolak.
     */
    public function test_active_user_email_allowed_but_duplicate_other_email_rejected()
    {
        $otherUser = $this->createUser([
            'email' => 'other_user@example.test',
        ]);

        $activeUser = $this->createUser([
            'email' => 'active_user@example.test',
        ]);

        // 6a: Keeping active user's own email succeeds
        $responseOwn = $this->actingAs($activeUser)
            ->put(route('profil.store'), [
                'view'     => 'data',
                'username' => $activeUser->username,
                'nama'     => 'Active Name',
                'email'    => 'active_user@example.test',
            ]);
        $responseOwn->assertSessionHasNoErrors();
        $responseOwn->assertRedirect(route('profil.index'));

        // 6b: Submitting another user's email fails validation
        $responseOther = $this->actingAs($activeUser)
            ->put(route('profil.store'), [
                'view'     => 'data',
                'username' => $activeUser->username,
                'nama'     => 'Active Name',
                'email'    => 'other_user@example.test',
            ]);
        $responseOther->assertSessionHasErrors(['email']);
    }
}

