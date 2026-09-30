<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Broadcast;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
uses(RefreshDatabase::class);

    /**
     * A basic test example.
     */

test('admin or owner can get chat settings', function(){

    $user1 = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' =>Hash::make('Password1234!'),
    ]);

    $user2 = User::factory()->create([
        'name' => 'illyana Rasputin',
        'email' => 'Magik@queen.com',
        'password' =>Hash::make('Password12366784!'),
    ]);

    $token = $user1->createToken('test-token')->plainTextToken;

    $response = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('api/chat/create/'. json_encode($user2->id), [
        'type' => 'PublicGroup',
        'name' => 'test'
    ]);

    $response->assertStatus(201)
    ->assertJson([
        'success' => true,
        'message' => 'chat created successfully',
        'data' => [
            'id' => $response->json('data.id'),
            'type' => 'PublicGroup',
            'name' => 'test',
        ],
        'members' => [$user1->id , $user2->id]
    ]);

    $this->assertDatabaseHas('chats', [
        'id' => $response->json('data.id'),
        'type' => 'PublicGroup',
    ]);

    $this->assertDatabaseHas('chat_members', [
        'chat_id' => $response->json('data.id'),
        'user_id' => $user1->id,
        'type' => 'owner',
    ]);

    $chatId = $response->json('data.id');

    $response2 = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson("api/one-to-n-settings/$chatId");

    $response2->assertStatus(200)
        ->assertJson([
            'success' => true,
            'settings' => $response2->json('settings')
        ]);
});

test('admin or owner can promote new admin', function(){

    $user1 = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' =>Hash::make('Password1234!'),
    ]);

    $user2 = User::factory()->create([
        'name' => 'illyana Rasputin',
        'email' => 'Magik@queen.com',
        'password' =>Hash::make('Password12366784!'),
    ]);

    $token = $user1->createToken('test-token')->plainTextToken;

    $response = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('api/chat/create/'. json_encode($user2->id), [
            'type' => 'PublicGroup',
            'name' => 'test'
        ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'chat created successfully',
            'data' => [
                'id' => $response->json('data.id'),
                'type' => 'PublicGroup',
                'name' => 'test',
            ],
            'members' => [$user1->id , $user2->id]
        ]);

    $this->assertDatabaseHas('chats', [
        'id' => $response->json('data.id'),
        'type' => 'PublicGroup',
    ]);

    $this->assertDatabaseHas('chat_members', [
        'chat_id' => $response->json('data.id'),
        'user_id' => $user1->id,
        'type' => 'owner',
    ]);

    $chatId = $response->json('data.id');

    $response2 = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->putJson("api/one-to-n-settings/$chatId/change-user-to-admin" , ['user_id' => $user2->id , 'type' => 'admin']);

    $response2->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'promoted successfully',
        ]);

    $this->assertDatabaseHas('chat_members', [
        'chat_id' => $response->json('data.id'),
        'user_id' => $user2->id ,
        'type' => 'admin',
    ]);
});

test('admin or owner can demote existing admin', function(){

    $user1 = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' =>Hash::make('Password1234!'),
    ]);

    $user2 = User::factory()->create([
        'name' => 'illyana Rasputin',
        'email' => 'Magik@queen.com',
        'password' =>Hash::make('Password12366784!'),
    ]);

    $token = $user1->createToken('test-token')->plainTextToken;

    $response = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('api/chat/create/'. json_encode($user2->id), [
            'type' => 'PublicGroup',
            'name' => 'test'
        ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'chat created successfully',
            'data' => [
                'id' => $response->json('data.id'),
                'type' => 'PublicGroup',
                'name' => 'test',
            ],
            'members' => [$user1->id , $user2->id]
        ]);

    $this->assertDatabaseHas('chats', [
        'id' => $response->json('data.id'),
        'type' => 'PublicGroup',
    ]);

    $this->assertDatabaseHas('chat_members', [
        'chat_id' => $response->json('data.id'),
        'user_id' => $user1->id,
        'type' => 'owner',
    ]);

    $chatId = $response->json('data.id');

    $response2 = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->putJson("api/one-to-n-settings/$chatId/change-user-to-admin" , ['user_id' => $user2->id , 'type' => 'admin']);

    $response2->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'promoted successfully',
        ]);

    $response3 = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->putJson("api/one-to-n-settings/$chatId/change-user-to-admin" , ['user_id' => $user2->id , 'type' => 'member']);

    $response3->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'demoted successfully',
        ]);

    $this->assertDatabaseHas('chat_members', [
        'chat_id' => $response->json('data.id'),
        'user_id' => $user2->id ,
        'type' => 'member',
    ]);
});

