<?php

namespace Tests\Feature;

use App\Models\Kelompok\MasterData\Kelompok;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class KelompokModuleTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('master_data');
    }

    public function test_kelompok_model_queries_correct_table()
    {
        $this->assertEquals('kelompok_m_kelompok', (new Kelompok())->getTable());
        $count = Kelompok::count();
        $this->assertIsInt($count);
    }

    public function test_kelompok_index_route_renders_successfully()
    {
        $user = User::create([
            'username' => 'test_user_' . uniqid(),
            'nama'     => 'Test Master Data User',
            'email'    => 'user_' . uniqid() . '@example.test',
            'password' => Hash::make('password123'),
            'no_telp'  => '081234567890',
            'status'   => true,
        ]);
        $user->givePermissionTo('master_data');

        $response = $this->actingAs($user)
            ->get(route('kelompok.master_data.kelompok.index'));

        $response->assertStatus(200);
        $response->assertSee('Master Data Kelompok');
    }
}

