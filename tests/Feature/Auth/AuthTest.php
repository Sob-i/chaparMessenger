<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\User;
uses(RefreshDatabase::class);

    /**
     * A basic test example.
     */

test('user can register with valid information', function () {

    $response = $this->postJson('/api/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'registered successfully',
        ])
        ->assertJsonStructure([
            'user' => ['id', 'name', 'email'],
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com',
        'name' => 'John Doe',
    ]);
});

test('user cant register with invalid information', function () {

    $response = $this->postJson('/api/register', [
        'email' => 'john@example.com',
        'password' => 'Password123!',
    ]);

    $response->assertStatus(422);

    $this->assertDatabaseMissing('users', [
        'email' => 'failed to register',
    ]);
});

test('user can login with valid information', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' =>Hash::make('Password123!'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'john@example.com',
        'password' => 'Password123!',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'logged in successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ],
            'token_type' => 'Bearer',
        ]);

    $responseData = $response->json();

    $this->assertNotNull($responseData['access_token']);

    $this->assertStringContainsString('|', $responseData['access_token']);
});

test('user cant login with invalid information', function () {

    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' =>Hash::make('Password1234!'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'john@example.com',
        'password' => 'Password123!',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Invalid email or password',
        ]);

    $responseData = $response->json();

    $this->assertArrayNotHasKey('access_token', $responseData);
    $this->assertNull($responseData['access_token'] ?? null);
});

test('user can logout', function () {

    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' =>Hash::make('Password1234!'),
    ]);

    $loginResponse = $this->postJson('/api/login', [
        'email' => 'john@example.com',
        'password' => 'Password1234!',
    ]);

    $token = $loginResponse->json('access_token');

    $loginResponse->assertStatus(200);

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)->postJson('/api/logout');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'logged out successfully',
        ]);

    $this->assertDatabaseCount('personal_access_tokens', 0);

});

