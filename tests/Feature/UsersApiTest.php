<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Enums\UserRole;

class UsersApiTest extends TestCase
{
    use RefreshDatabase;

    
    public function test_register_successfully() 
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => UserRole::ADMIN->value,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'User registered successfully',
        ]);

        $this->assertDatabaseHas('users',[
            'email' => 'admin@example.com',
        ]);
    }

    
    public function test_admin_register_validation_fail()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'password_confirmation' => 'wrong_password',
            'role' => UserRole::ADMIN->value,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => false,
                'message' => 'The password field confirmation does not match.',
        ]);
    }

    
    public function test_admin_login_successfully()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'token',
            ]);
    }

    
    public function test_admin_login_fail(){
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'no@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => false,
                'message' => 'Invalid credentials',
            ]);

    }

}
