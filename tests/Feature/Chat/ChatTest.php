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
        ->getJson('api/chats/search' , [
            'searchKey' => 'mmd',
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'chats' => [
                [
                    'id'   => 7,
                    'type' => 'PublicGroup',
                    'name' => 'group mmd 1',
                ],
                [
                    'id'   => 9,
                    'type' => 'PublicChannel',
                    'name' => 'mmd',
                ],
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
