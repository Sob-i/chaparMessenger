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

    $token = $user1->createToken('test-token')->plainTextToken;

    $response = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('api/chat/create/'. json_encode($user2->id), [
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

    $token = $user1->createToken('test-token')->plainTextToken;

    $first = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('api/chat/create/'. json_encode($user2->id), [
            'type' => 'private',
        ]);

    $first->assertStatus(201);
    $firstChatId = $first->json('data.id');

    $second = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('api/chat/create/'. json_encode($user2->id), [
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

test('user can create public group chat with a name', function(){

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

    $token = $user1->createToken('test-token')->plainTextToken;

    $response = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('api/chat/create/'. json_encode([$user2->id , $user3->id]), [
            'type' => 'PublicGroup',
            'name' => 'group chat 1',
        ]);

    $response->assertStatus(201)
    ->assertJson([
        'success' => true,
        'message' => 'chat created successfully',
        'data' => [
            'id' => $response->json('data.id'),
            'type' => 'PublicGroup',
            'name' => 'group chat 1',
        ],
        'members' => [$user1->id , $user2->id , $user3->id]
    ]);

    $this->assertDatabaseHas('chats', [
        'id' => $response->json('data.id'),
        'type' => 'PublicGroup',
    ]);
});

test('user can create public channel with a name', function(){

    $user1 = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' =>Hash::make('Password1234!'),
    ]);

    $token = $user1->createToken('test-token')->plainTextToken;

    $response = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('api/chat/create/'. json_encode([]), [
        'type' => 'PublicChannel',
        'name' => 'mmd',
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'chat created successfully',
            'data' => [
                'id' => $response->json('data.id'),
                'type' => 'PublicChannel',
                'name' => 'mmd',
            ],
            'members' => [$user1->id]
        ]);

    $this->assertDatabaseHas('chats', [
        'id' => $response->json('data.id'),
        'type' => 'PublicChannel',
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

    $token = $user1->createToken('test-token')->plainTextToken;

    $response = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('api/chat/create/'. json_encode([$user2->id , $user3->id]), [
        'type' => 'PublicGroup',
    ]);

    $response->assertStatus(422)
        ->assertJson([
           'success' => false,
            'message' => 'Please enter a name for group or channel',
        ]);

    $this->assertDatabaseMissing('chats', [
        'id' => '4',
        'type' => 'PublicGroup',
    ]);

    $response = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('api/chat/create/'. json_encode([$user2->id , $user3->id]), [
            'type' => 'PrivateChannel',
        ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Please enter a name for group or channel',
        ]);

    $this->assertDatabaseMissing('chats', [
        'id' => '5',
        'type' => 'PrivateChannel',
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

    $token = $user1->createToken('test-token')->plainTextToken;

    $response = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('api/chat/create/' . json_encode([$user2->id, $user3->id]), [
        'type' => 'PrivateGroup',
        'name' => 'test group',
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
            'type' => 'PrivateGroup',
            'name' => 'test group',
        ],
        'members' => [$user1->id, $user2->id, $user3->id]
    ]);

    $this->assertDatabaseHas('chats', [
        'id' => $chatId,
        'type' => 'PrivateGroup',
    ]);

    $this->assertDatabaseHas('chat_members', [
        'chat_id' => $chatId,
        'user_id' => $user1->id,
    ]);

    $response = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('api/chat/create/' . json_encode($user2->id), [
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

test('user can search and get chats that exists', function () {

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

    $token = $user1->createToken('test-token')->plainTextToken;

    $response = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('api/chat/create/'. json_encode($user2->id), [
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



    $response = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('api/chat/create/'. json_encode([$user2->id , $user3->id]), [
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



    $response = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('api/chat/create/'. json_encode([]), [
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
