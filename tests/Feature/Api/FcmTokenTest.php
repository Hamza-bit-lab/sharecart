<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\FcmToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class FcmTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_store_fcm_token()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/fcm-token', [
            'token' => 'test-fcm-token',
            'device_id' => 'device-123',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Token saved']);

        $this->assertDatabaseHas('fcm_tokens', [
            'user_id' => $user->id,
            'token' => 'test-fcm-token',
            'device_id' => 'device-123',
        ]);
    }

    public function test_can_update_existing_token_owner()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        FcmToken::create([
            'user_id' => $user1->id,
            'token' => 'shared-token',
        ]);

        Sanctum::actingAs($user2);

        $response = $this->postJson('/api/fcm-token', [
            'token' => 'shared-token',
        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('fcm_tokens', [
            'user_id' => $user2->id,
            'token' => 'shared-token',
        ]);
        
        $this->assertEquals(1, FcmToken::where('token', 'shared-token')->count());
    }
}
