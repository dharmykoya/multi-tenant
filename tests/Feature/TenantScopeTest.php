<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\Factories\Factory;
use \App\Models\User;
use \App\Models\Tenant;

class TenantScopeTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_a_model_has_a_tenant_id_on_the_migration()
    {
        // $this->withoutExceptionHandling();
        $this->withExceptionHandling();

        // $this->assertTrue(true);
        $now = now();
        $this->artisan('make:model Test -m');

        // find migration file and check it has a tenant_id on it
        $filename = $now->year . '_' . $now->format('m') . '_' . $now->format('d') . '_' . $now->format('H') . $now->format('i') . $now->format('s') . '_create_tests_table.php';


        \Log::info($filename);

        $this->assertTrue(File::exists(database_path('migrations/' .$filename)));
        $this->assertStringContainsString('$table->unsignedBigInteger(\'tenant_id\')->index();', File::get(database_path('migrations/' . $filename)));

        // clean up
        File::delete(database_path('migrations/'.$filename));
        File::delete(app_path('Models/Test.php'));
    }


    public function test_a_user_can_only_see_users_in_the_same_tenant()
    {
        $this->withExceptionHandling();

        $tenant1 = Tenant::factory()->create();


        $tenant2 = Tenant::factory()->create();

        $user1 = User::factory(10)->create([
            'tenant_id' => $tenant1
        ]);

        $user2 =
        User::factory(10)->create([
            'tenant_id' => $tenant2
        ]);

        auth()->login($user1[0]);

        $this->assertEquals(10, User::count());
    }

    public function test_a_user_can_only_create_user_in_his_tenant()
    {
        // $this->withExceptionHandling();
        $this->withoutExceptionHandling();

        // assertions
        $tenant1 = Tenant::factory()->create();

        $user1 = User::factory()->create([
            'tenant_id' => $tenant1
        ]);

        \Log::info($user1);

        auth()->login($user1);

        // $createdUser = User::factory()->create([
        //     'tenant_id' => $user1->tenant_id
        // ]);
        $createdUser = User::factory()->create();

        \Log::info($createdUser);

        $this->assertTrue($createdUser->tenant_id == $user1->tenant_id);
    }



}
