<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
uses(RefreshDatabase::class);

    /**
     * A basic test example.
     */

test('user can get its profile', function () {

    $user1 = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' =>Hash::make('Password1234!'),
    ]);

    $this->actingAs($user1);

    $response = $this->getJson('api/user_profile');

    $response->assertStatus(200)->
    assertJson([
        'success' => true,
        'data' => [
            'user_id' => $user1->id,
            'user_name' => '',
            'avatar' => '',
            'bio' => '',
            'phone' => '',
        ],
    ]);

});

test('user can edit its profile settings', function(){

    $user1 = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' =>Hash::make('Password1234!'),
    ]);

    $this->actingAs($user1);

    $data = [
        'user_name' => 'johnny',
        'avatar' => fake()->imageUrl(),
        'bio' => 'lone wolf aooooooooooooooooooooooooooo',
        'phone' => '09377805100',
    ];
    $response = $this->putJson('api/user_profile/edit', [
        'user_id' => $user1->id ,
        'user_name' => $data['user_name'],
        'avatar' => $data['avatar'],
        'bio' => $data['bio'],
        'phone' => $data['phone'],
    ]);

    $response->assertStatus(201)
    ->assertJson([
        'success' => true,
        'message' => 'Profile edited successfully',
    ]);

    $this->assertDatabaseHas('user_profile', [
        'user_id' => $user1->id,
        'user_name' => '@'.$data['user_name'],
        'avatar' => $data['avatar'],
        'bio' => $data['bio'],
        'phone' => $data['phone'],
    ]);

});

test('user cant edit another user profile settings', function(){

    $user1 = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' =>Hash::make('Password1234!'),
    ]);

    $user2 = User::factory()->create([
        'name' => 'mmd scott',
        'email' => 'mmd@scott.com',
        'password' =>Hash::make('Password1234!'),
    ]);

    $this->actingAs($user1);

    $data = [
        'user_name' => 'johnny',
        'avatar' => fake()->imageUrl(),
        'bio' => 'lone wolf aooooooooooooooooooooooooooo',
        'phone' => '09377805100',
    ];

    $response = $this->putJson('api/user_profile/edit', [
        'user_id' => $user2->id ,
        'user_name' => $data['user_name'],
        'avatar' => $data['avatar'],
        'bio' => $data['bio'],
        'phone' => $data['phone'],
    ]);

    $this->assertDatabaseMissing('user_profile', [
        'user_id' => $user2->id,
        'user_name' => '@'.$data['user_name'],
        'avatar' => $data['avatar'],
        'bio' => $data['bio'],
        'phone' => $data['phone'],
    ]);

});

