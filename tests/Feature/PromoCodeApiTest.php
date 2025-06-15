<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Enums\UserRole;
use App\Enums\PromoCodeType;
use App\Models\PromoCode;

class PromoCodeApiTest extends TestCase
{
   use RefreshDatabase;

   public function test_create_promo_code_successfully()
   {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $user1 = User::factory()->create(['role' => UserRole::USER]);
        $user2 = User::factory()->create(['role' => UserRole::USER]);
        $data = [
            'code' => 'TEST1',
            'type' => PromoCodeType::VALUE->value,
            'amount' => 10,
            'max_usage' => 100,
            'user_max_usage' => 2,
            'expires_at' => now()->addDays(5)->toDateTimeString(),
            'user_ids' => [$user1->id, $user2->id],
        ];

        $this->actingAs($admin)
            ->postJson('/api/promo-codes/create', $data)
            ->assertStatus(201)
            ->assertJsonFragment(['message' => 'Promo code created successfully']);

        $this->assertDatabaseHas('promo_codes', ['code' => 'TEST1']);
   }

   public function test_create_promo_code_successfully_with_empty_code()
   {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $user1 = User::factory()->create(['role' => UserRole::USER]);
        $user2 = User::factory()->create(['role' => UserRole::USER]);
        $data = [
            'type' => PromoCodeType::VALUE->value,
            'amount' => 10,
            'max_usage' => 100,
            'user_max_usage' => 2,
            'expires_at' => now()->addDays(5)->toDateTimeString(),
            'user_ids' => [$user1->id, $user2->id],
        ];

        $this->actingAs($admin)
            ->postJson('/api/promo-codes/create', $data)
            ->assertStatus(201)
            ->assertJsonFragment(['message' => 'Promo code created successfully']);
   }

   public function test_create_promo_code_validation_type()
   {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $user1 = User::factory()->create(['role' => UserRole::USER]);
        $user2 = User::factory()->create(['role' => UserRole::USER]);
        $data = [
            'code' => 'TEST1',
            'type' => 'nothing',
            'amount' => 10,
            'max_usage' => 100,
            'user_max_usage' => 2,
            'expires_at' => now()->addDays(5)->toDateTimeString(),
            'user_ids' => [$user1->id, $user2->id],
        ];

        $this->actingAs($admin)
            ->postJson('/api/promo-codes/create', $data)
            ->assertStatus(422)
            ->assertJson(["status" => false,
                          "message" => "The selected type is invalid."
            ]);

   }

   public function test_create_promo_code_validation_empty_users()
   {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        
        $data = [
            'code' => 'TEST1',
            'type' => PromoCodeType::VALUE->value,
            'amount' => 10,
            'max_usage' => 100,
            'user_max_usage' => 2,
            'expires_at' => now()->addDays(5)->toDateTimeString(),
            'user_ids' => [],
        ];

        $this->actingAs($admin)
            ->postJson('/api/promo-codes/create', $data)
            ->assertStatus(422)
            ->assertJson(["status" => false,
                          "message" => "The user ids field is required."
            ]);
   }

   public function test_create_promo_code_validation_invalid_user()
   {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        
        $data = [
            'code' => 'TEST1',
            'type' => PromoCodeType::VALUE->value,
            'amount' => 10,
            'max_usage' => 100,
            'user_max_usage' => 2,
            'expires_at' => now()->addDays(5)->toDateTimeString(),
            'user_ids' => [$admin->id],
        ];

        $this->actingAs($admin)
            ->postJson('/api/promo-codes/create', $data)
            ->assertStatus(422)
            ->assertJson(["status" => false,
                          "message" => "Some selected users are not regular users.",
                          'invalid_user_ids' => [$admin->id]
            ]);
   }

   public function test_redeem_promo_code_successfully()
   {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $user1 = User::factory()->create(['role' => UserRole::USER]);
        $user2 = User::factory()->create(['role' => UserRole::USER]);

        $promo = PromoCode::factory()->create(['code' => "MOMO1"]);
        $promo->users()->sync([$user1->id, $user2->id]);

        $data = [
            'price' => 100,
            'code' => 'MOMO1'
        ];

        $this->actingAs($user1)
            ->postJson("/api/promo-codes/redeem", $data)
            ->assertStatus(200)
            ->assertJsonFragment(["message" => "redeem Promo Code"]);
   }

   public function test_redeem_promo_code_validation_invalid_code()
   {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $user1 = User::factory()->create(['role' => UserRole::USER]);
        $user2 = User::factory()->create(['role' => UserRole::USER]);

        $promo = PromoCode::factory()->create(['code' => "TEST1"]);
        $promo->users()->sync([$user1->id, $user2->id]);

        $data = [
            'price' => 100,
            'code' => 'TEST2'
        ];

        $this->actingAs($user1)
            ->postJson("/api/promo-codes/redeem", $data)
            ->assertStatus(422)
            ->assertJson(['status' => false,
                          "message" => "The selected code is invalid."]);

   }


   public function test_redeem_promo_code_validation_code_expired()
   {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $user1 = User::factory()->create(['role' => UserRole::USER]);
        $user2 = User::factory()->create(['role' => UserRole::USER]);

        $promo = PromoCode::factory()
            ->create(['code' => "TEST1", 'expires_at' => now()->subDays(1)]);
        $promo->users()->sync([$user1->id, $user2->id]);

        $data = [
            'price' => 100,
            'code' => 'TEST1'
        ];

        $this->actingAs($user1)
            ->postJson("/api/promo-codes/redeem", $data)
            ->assertStatus(422)
            ->assertJson(['status' => false,
                          "message" => "The selected code is expired"]);

   }

    public function test_redeem_promo_code_validation_invalid_user()
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $user1 = User::factory()->create(['role' => UserRole::USER]);
        $user2 = User::factory()->create(['role' => UserRole::USER]);

        $promo = PromoCode::factory()
            ->create(['code' => "TEST1"]);
        $promo->users()->sync([$user2->id]);

        $data = [
            'price' => 100,
            'code' => 'TEST1'
        ];

        $this->actingAs($user1)
            ->postJson("/api/promo-codes/redeem", $data)
            ->assertStatus(422)
            ->assertJson(['status' => false,
                          "message" => "The selected code invalid for the current user"]);
    }
    
    public function test_redeem_promo_code_validation_max_usage()
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $user1 = User::factory()->create(['role' => UserRole::USER]);
        $user2 = User::factory()->create(['role' => UserRole::USER]);

        $promo = PromoCode::factory()
                ->create(['code' => "TEST1", 'usage' => 10, 'max_usage' => 10]);
        $promo->users()->sync([$user1->id]);

        $data = [
            'price' => 100,
            'code' => 'TEST1'
        ];

        $this->actingAs($user1)
            ->postJson("/api/promo-codes/redeem", $data)
            ->assertStatus(422)
            ->assertJson(['status' => false,
                          "message" => "The selected code exceeded max usage"]);

   }

    public function test_redeem_promo_code_validation_user_max_usage()
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $user1 = User::factory()->create(['role' => UserRole::USER]);
        $user2 = User::factory()->create(['role' => UserRole::USER]);

        $promo = PromoCode::factory()
                ->create(['code' => "TEST1", 'user_max_usage' => 1]);
         $promo->users()->attach($user1->id, ['times_redeemed' => 1]);

        $data = [
            'price' => 100,
            'code' => 'TEST1'
        ];

        $this->actingAs($user1)
            ->postJson("/api/promo-codes/redeem", $data)
            ->assertStatus(422)
            ->assertJson(['status' => false,
                          "message" => "The selected code exceeded number of usage for the current user"]);

   }
}
