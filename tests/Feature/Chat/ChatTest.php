<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
uses(RefreshDatabase::class);

    /**
     * A basic test example.
     */

test('user can create private chat without a name', function(){

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

    $this->actingAs($user1);

    $response = $this->postJson('api/chat/create/'. json_encode(['user_id' => $user1->id,'member' =>$user2->id]), [
        'type' => 'private',
    ]);

    $response->assertStatus(201)
    ->assertJson([
        'success' => true,
        'message' => 'chat created successfully',
        'data' => [
            'id' => $response->json('data.id'),
            'type' => 'private',
            'name' => null,
        ],
        'members' => [$user1->id , $user2->id]
    ]);

    $this->assertDatabaseHas('chats', [
        'id' => $response->json('data.id'),
        'type' => 'private',
    ]);

    $this->assertDatabaseHas('chat_members', [
        'chat_id' => $response->json('data.id'),
        'user_id' => $user2->id,
        'type' => 'PrivateMember',
    ]);
});

test('user cant create same private chat twice', function () {

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

    $this->actingAs($user1);

    $first = $this->postJson('api/chat/create/'. json_encode(['user_id' => $user1->id,'member' =>$user2->id]), [
        'type' => 'private',
    ]);

    $first->assertStatus(201);
    $firstChatId = $first->json('data.id');

    $second = $this->postJson('api/chat/create/'. json_encode(['user_id' => $user1->id,'member' =>$user2->id]), [
        'type' => 'private',
    ]);

    $second->assertStatus(409)
    ->assertJson([
        'success' => false,
        'message' => 'Chat already exists',
    ]);

    $this->assertDatabaseCount('chats', 1);

    $this->assertDatabaseCount('chat_members', 2);
});

test('user can create group chat with a name', function(){

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

    $user3 = User::factory()->create([
        'name' => 'mark specter',
        'email' => 'moon@knight.com',
        'password' =>Hash::make('Password1236adawd6784!'),
    ]);

    $this->actingAs($user1);

    $response = $this->postJson('api/chat/create/'. json_encode([$user1->id , $user2->id , $user3->id]), [
        'type' => 'group',
        'name' => 'group chat 1',
    ]);

    $response->assertStatus(201)
    ->assertJson([
        'success' => true,
        'message' => 'chat created successfully',
        'data' => [
            'id' => $response->json('data.id'),
            'type' => 'group',
            'name' => 'group chat 1',
        ],
        'members' => [$user1->id , $user2->id , $user3->id]
    ]);

    $this->assertDatabaseHas('chats', [
        'id' => $response->json('data.id'),
        'type' => 'group',
    ]);
});

test('user can create channel with a name', function(){

    $user1 = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' =>Hash::make('Password1234!'),
    ]);

    $this->actingAs($user1);

    $response = $this->postJson('api/chat/create/'. json_encode([$user1->id]), [
        'type' => 'channel',
        'name' => 'mmd',
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'chat created successfully',
            'data' => [
                'id' => $response->json('data.id'),
                'type' => 'channel',
                'name' => 'mmd',
            ],
            'members' => [$user1->id]
        ]);

    $this->assertDatabaseHas('chats', [
        'id' => $response->json('data.id'),
        'type' => 'channel',
    ]);

    $this->assertDatabaseHas('chat_members', [
        'chat_id' => $response->json('data.id'),
        'user_id' => $user1->id,
        'type' => 'admin'
    ]);
});

test('user cant create a group chat or channel without a name', function () {

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

    $user3 = User::factory()->create([
        'name' => 'mark specter',
        'email' => 'moon@knight.com',
        'password' =>Hash::make('Password1236adawd6784!'),
    ]);

    $this->actingAs($user1);

    $response = $this->postJson('api/chat/create/'. json_encode([$user1->id , $user2->id , $user3->id]), [
        'type' => 'group',
    ]);

    $response->assertStatus(422)
        ->assertJson([
           'success' => false,
            'message' => 'Please enter a name for group or channel',
        ]);

    $this->assertDatabaseMissing('chats', [
        'id' => '4',
        'type' => 'group',
    ]);
});

test('user can get its chats', function () {

    $user1 = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => Hash::make('Password1234!'),
    ]);

    $user2 = User::factory()->create([
        'name' => 'illyana Rasputin',
        'email' => 'Magik@queen.com',
        'password' => Hash::make('Password12366784!'),
    ]);

    $user3 = User::factory()->create([
        'name' => 'mark specter',
        'email' => 'moon@knight.com',
        'password' => Hash::make('Password1236adawd6784!'),
    ]);

    $this->actingAs($user1);

    $response = $this->postJson('api/chat/create/' . json_encode([$user1->id, $user2->id, $user3->id]), [
        'type' => 'group',
        'name' => 'dek to archang',
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'chat created successfully',
        ]);

    $chatId = $response->json('data.id');

    $response->assertJson([
        'data' => [
            'id' => $chatId,
            'type' => 'group',
            'name' => 'dek to archang',
        ],
        'members' => [$user1->id, $user2->id, $user3->id]
    ]);

    $this->assertDatabaseHas('chats', [
        'id' => $chatId,
        'type' => 'group',
    ]);

    $this->assertDatabaseHas('chat_members', [
        'chat_id' => $chatId,
        'user_id' => $user1->id,
    ]);

    $response = $this->postJson('api/chat/create/' . json_encode([$user1->id, $user2->id]), [
        'type' => 'private',
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'chat created successfully',
        ]);

    $chatId2 = $response->json('data.id');

    $response->assertJson([
        'data' => [
            'id' => $chatId2,
            'type' => 'private',
            'name' => null,
        ],
        'members' => [
            $user1->id,
            $user2->id
        ]
    ]);

    $this->assertDatabaseHas('chats', [
        'id' => $chatId2,
        'type' => 'private',
    ]);

    $this->assertDatabaseHas('chat_members', [
        'chat_id' => $chatId2,
        'user_id' => $user1->id,
    ]);

    $response = $this->getJson('api/chats');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);

});
