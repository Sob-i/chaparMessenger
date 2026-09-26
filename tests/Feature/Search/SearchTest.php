<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
uses(RefreshDatabase::class);

    /**
     * A basic test example.
     */

test('user can search and get public chats that exists', function () {

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

    $data = [
        'user_name' => 'johnny',
        'avatar' => fake()->imageUrl(),
        'bio' => 'lone wolf aooooooooooooooooooooooooooo',
        'phone' => '09377805100',
    ];

    $response = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->putJson('api/user-profile/edit', [
            'user_id' => $user1->id ,
            'user_name' => $data['user_name'],
            'avatar' => $data['avatar'],
            'bio' => $data['bio'],
            'phone' => $data['phone'],
        ]);

    $response = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('api/chat/create/'. json_encode([$user2->id , $user3->id]), [
            'type' => 'PublicGroup',
            'name' => 'group mmd 1',
        ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'chat created successfully',
            'data' => [
                'id' => $response->json('data.id'),
                'type' => 'PublicGroup',
                'name' => 'group mmd 1',
            ],
            'members' => [$user1->id , $user2->id , $user3->id]
        ]);

    $this->assertDatabaseHas('chats', [
        'id' => $response->json('data.id'),
        'type' => 'PublicGroup',
    ]);

    $response = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('api/chat/create/'. json_encode([$user2->id , $user3->id]), [
            'type' => 'PrivateGroup',
            'name' => 'group chat 2',
        ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'chat created successfully',
            'data' => [
                'id' => $response->json('data.id'),
                'type' => 'PrivateGroup',
                'name' => 'group chat 2',
            ],
            'members' => [$user1->id , $user2->id , $user3->id]
        ]);

    $this->assertDatabaseHas('chats', [
        'id' => $response->json('data.id'),
        'type' => 'PrivateGroup',
    ]);

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


    $response = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('api/chat/create/'. json_encode([]), [
            'type' => 'PrivateChannel',
            'name' => 'mmd2',
        ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'chat created successfully',
            'data' => [
                'id' => $response->json('data.id'),
                'type' => 'PrivateChannel',
                'name' => 'mmd2',
            ],
            'members' => [$user1->id]
        ]);

    $this->assertDatabaseHas('chats', [
        'id' => $response->json('data.id'),
        'type' => 'PrivateChannel',
    ]);

    $this->assertDatabaseHas('chat_members', [
        'chat_id' => $response->json('data.id'),
        'user_id' => $user1->id,
        'type' => 'admin'
    ]);

    $response = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson('api/search' , [
            'searchKey' => 'mmd',
        ]);

    $response->assertStatus(200)
        ->assertJson(['success' => true,])
        ->assertJsonStructure([
            'success',
            'result' => [
                'chats' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'type',
                            'name',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                    'total',
                    'per_page',
                ],
                'users',
                'chatMessages',
            ],
        ]);

    $response2 = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson('api/search' , [
            'searchKey' => '@j',
        ]);

    $response2->assertStatus(200)
        ->assertJson(['success' => true,])
        ->assertJsonStructure([
            'success',
            'result' => [
                'chats' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'type',
                            'name',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                    'total',
                    'per_page',
                ],
                'users',
                'chatMessages',
            ],
        ]);
});

test('user can search and get chat messages that is a member of', function () {

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

    $this->actingAs($user1);

    $responseChat1 = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('api/chat/create/'. json_encode($user2->id), [
            'type' => 'private',
        ]);

    $responseChat1->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'chat created successfully',
            'data' => [
                'id' => $responseChat1->json('data.id'),
                'type' => 'private',
                'name' => null,
            ],
            'members' => [$user1->id , $user2->id]
        ]);

    $this->assertDatabaseHas('chats', [
        'id' => $responseChat1->json('data.id'),
        'type' => 'private',
    ]);

    $this->assertDatabaseHas('chat_members', [
        'chat_id' => $responseChat1->json('data.id'),
        'user_id' => $user2->id,
        'type' => 'PrivateMember',
    ]);

    $responseMessage = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('api/chat/send-message' , [
            'chat_id' => $responseChat1->json('data.id'),
            'receiver_id' => $user2->id ,
            'message' => 'first message to user 2' ,
            'attachments' => null ,
            'type' => 'message' ,
        ]);

    $responseMessage->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'message sent successfully',
            'data' => $responseMessage->json('data')
        ]);

    $this->assertDatabaseHas('chat_messages', [
        'chat_id' => $responseChat1->json('data.id') ,
        'sender_id' => $user1->id ,
        'receiver_id' => $user2->id ,
        'message' => 'first message to user 2' ,
        'attachments' => null ,
        'type' => 'message' ,
    ]);

    $this->actingAs($user2);

    $token2 = $user2->createToken('test-token')->plainTextToken;

    $responseChat2 = $this
        ->withHeader('Authorization', 'Bearer ' . $token2)
        ->postJson('api/chat/create/'. json_encode($user3->id), [
            'type' => 'private',
        ]);

    $responseChat2->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'chat created successfully',
            'data' => [
                'id' => $responseChat2->json('data.id'),
                'type' => 'private',
                'name' => null,
            ],
            'members' => [$user2->id , $user3->id]
        ]);

    $this->assertDatabaseHas('chats', [
        'id' => $responseChat2->json('data.id'),
        'type' => 'private',
    ]);

    $this->assertDatabaseHas('chat_members', [
        'chat_id' => $responseChat2->json('data.id'),
        'user_id' => $user2->id,
        'type' => 'PrivateMember',
    ]);

    $responseMessage = $this
        ->withHeader('Authorization', 'Bearer ' . $token2)
        ->postJson('api/chat/send-message' , [
            'chat_id' => $responseChat2->json('data.id'),
            'receiver_id' => $user3->id ,
            'message' => 'first message' ,
            'attachments' => null ,
            'type' => 'message' ,
        ]);

    $responseMessage->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'message sent successfully',
            'data' => $responseMessage->json('data')
        ]);

    $this->assertDatabaseHas('chat_messages', [
        'chat_id' => $responseChat2->json('data.id') ,
        'sender_id' => $user2->id ,
        'receiver_id' => $user3->id ,
        'message' => 'first message' ,
        'attachments' => null ,
        'type' => 'message' ,
    ]);

    $this->actingAs($user1);

    $chatId = $responseChat1->json('data.id');

    $response = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson("api/chat/$chatId/search" , [
            'searchKey' => 'first message',
        ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
                'messages' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'chat_id',
                            'sender_id',
                            'receiver_id',
                            'message',
                            'attachments',
                            'type',
                            'reply_to_user',
                            'reply_to_message',
                            'created_at',
                            'updated_at',
                        ],
                ],
            ],
        ]);

    $chatId = $responseChat2->json('data.id');

    $response = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson("api/chat/$chatId/search" , [
            'searchKey' => 'first message',
        ]);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'unauthorized',
        ]);
});
