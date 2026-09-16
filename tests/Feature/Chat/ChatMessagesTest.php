<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
uses(RefreshDatabase::class);

    /**
     * A basic test example.
     */

test('user can send message to a chat', function(){

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
        'user_id' => $user1->id,
        'type' => 'PrivateMember',
    ]);

    $responseMessage = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('api/chat/send-message' , [
        'chat_id' => $response->json('data.id'),
        'receiver_id' => $user2->id ,
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
        'chat_id' => $response->json('data.id') ,
        'sender_id' => $user1->id ,
        'receiver_id' => $user2->id ,
        'message' => 'first message' ,
        'attachments' => null ,
        'type' => 'message' ,
    ]);

});

test('user cant send message using some one else token', function () {

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
        'name' => 'Hacker',
        'email' => 'hacker@hack.com',
        'password' =>Hash::make('Password12366784!'),
    ]);

    $token3 = $user3->createToken('test-token')->plainTextToken;

    $token1 = $user1->createToken('test-token')->plainTextToken;

    $response = $this
        ->withHeader('Authorization', 'Bearer ' . $token3)
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
            'members' => [$user3->id , $user2->id]
        ]);

    $this->assertDatabaseHas('chats', [
        'id' => $response->json('data.id'),
        'type' => 'private',
    ]);

    $this->assertDatabaseHas('chat_members', [
        'chat_id' => $response->json('data.id'),
        'user_id' => $user3->id,
        'type' => 'PrivateMember',
    ]);

    $responseMessage = $this
        ->withHeader('Authorization', 'Bearer ' . $token1)
        ->postJson('api/chat/send-message' , [
            'chat_id' => $response->json('data.id'),
            'receiver_id' => $user2->id ,
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
        'chat_id' => $response->json('data.id') ,
        'sender_id' => $user3->id ,
        'receiver_id' => $user2->id ,
        'message' => 'first message' ,
        'attachments' => null ,
        'type' => 'message' ,
    ]);
});

test('user cant send message to a chat with invalid info', function(){

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
        'user_id' => $user1->id,
        'type' => 'PrivateMember',
    ]);


    $responseMessage = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('api/chat/send-message' , [
        'chat_id' => 1000,
        'receiver_id' => 200 ,
        'message' => 'first message' ,
        'attachments' => null ,
        'type' => 'aownmdonawd' ,
    ]);

    $responseMessage->assertStatus(422);

    $this->assertDatabaseMissing('chat_messages', [
        'chat_id' => $response->json('data.id') ,
        'sender_id' => $user1->id ,
        'receiver_id' => $user2->id ,
        'message' => 'first message' ,
        'attachments' => null ,
        'type' => 'message' ,
    ]);

});

test('user can send message to a chat with attachment', function(){

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

    $token = $user1->createToken('test-token')->plainTextToken;

    $response = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('api/chat/create/' . json_encode($user2->id), [
        'type' => 'private',
    ]);

    $response->assertStatus(201);

    $chatId = $response->json('data.id');

    $fileUrl = 'https://example.com/uploads/test-file.jpg';

    $responseMessage = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('api/chat/send-message', [
        'chat_id' => $chatId,
        'receiver_id' => $user2->id,
        'message' => 'first message',
        'attachments' => $fileUrl,
        'type' => 'message',
    ]);

    $responseMessage->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'message sent successfully',
        ]);

    $this->assertDatabaseHas('chat_messages', [
        'chat_id' => $chatId,
        'sender_id' => $user1->id,
        'receiver_id' => $user2->id,
        'message' => 'first message',
        'attachments' => $fileUrl,
        'type' => 'message',
    ]);

    $responseMessage->assertJsonPath('data.attachments', $fileUrl);

});

test('user can get chat messages', function () {

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
    $token2 = $user2->createToken('test-token')->plainTextToken;

    $this->actingAs($user1, 'sanctum');

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
        'user_id' => $user1->id,
        'type' => 'PrivateMember',
    ]);

    $responseMessage = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('api/chat/send-message' , [
        'chat_id' => $response->json('data.id'),
        'receiver_id' => $user2->id ,
        'message' => 'first message' ,
        'attachments' => null ,
        'type' => 'message' ,
    ]);

    $this->actingAs($user2, 'sanctum');

    $responseMessage2 = $this
        ->withHeader('Authorization', 'Bearer ' . $token2)
        ->postJson('api/chat/send-message' , [
        'chat_id' => $response->json('data.id'),
        'receiver_id' => $user1->id ,
        'message' => 'second message' ,
        'attachments' => null ,
        'type' => 'reply' ,
        'reply_to_message' => $responseMessage->json('data.id') ,
        'reply_to_user' => $user1->id,
    ]);

    $responseMessage->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'message sent successfully',
            'data' => $responseMessage->json('data')
        ]);

    $this->assertDatabaseHas('chat_messages', [
        'chat_id' => $response->json('data.id') ,
        'sender_id' => $user1->id ,
        'receiver_id' => $user2->id ,
        'message' => 'first message' ,
        'attachments' => null ,
        'type' => 'message' ,
    ]);

    $id = $response->json('data.id');

    $this->actingAs($user1, 'sanctum');

    $responseGetMessage = $this->getJson("api/chat/$id");

    $responseGetMessage->assertStatus(200)
        ->assertJsonFragment([
            'success' => true,
        ])
        ->assertJsonFragment([
            'chat_id' => $response->json('data.id'),
            'sender_id' => $user1->id,
            'receiver_id' => $user2->id,
            'message' => 'first message',
            'type' => 'message',
        ])
        ->assertJsonFragment([
            'chat_id' => $response->json('data.id'),
            'sender_id' => $user2->id,
            'receiver_id' => $user1->id,
            'message' => 'first message',
            'type' => 'message',
            'reply_to_message' => null,
            'reply_to_user' => null,
        ])
        ->assertJsonFragment([
            'chat_id' => $response->json('data.id'),
            'sender_id' => $user2->id,
            'receiver_id' => $user1->id,
            'message' => 'second message',
            'type' => 'reply',
            'reply_to_message' => $responseMessage->json('data.id') ,
            'reply_to_user' => $user1->id,
        ]);

    $responseGetMessage->assertJsonStructure([
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
                'created_at',
                'updated_at',
                'sender_info' => [
                    'id',
                    'name',
                ],
                'receiver_info' => [
                    'id',
                    'name',
                ],
                'reply_to_message' ,
                'reply_to_user' ,
            ]
        ]
    ]);
});

test('user cant get another user chat messages', function () {

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
        'name' => 'John Doe fake',
        'email' => 'johnfake@example.com',
        'password' =>Hash::make('Password1234!'),
    ]);

    $token = $user1->createToken('test-token')->plainTextToken;
    $token2 = $user2->createToken('test-token')->plainTextToken;

    $this->actingAs($user1, 'sanctum');

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
        'user_id' => $user1->id,
        'type' => 'PrivateMember',
    ]);

    $responseMessage = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('api/chat/send-message' , [
            'chat_id' => $response->json('data.id'),
            'receiver_id' => $user2->id ,
            'message' => 'first message' ,
            'attachments' => null ,
            'type' => 'message' ,
        ]);

    $this->actingAs($user2, 'sanctum');

    $responseMessage2 = $this
        ->withHeader('Authorization', 'Bearer ' . $token2)
        ->postJson('api/chat/send-message' , [
            'chat_id' => $response->json('data.id'),
            'receiver_id' => $user1->id ,
            'message' => 'second message' ,
            'attachments' => null ,
            'type' => 'reply' ,
            'reply_to_message' => $responseMessage->json('data.id') ,
            'reply_to_user' => $user1->id,
        ]);

    $responseMessage->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'message sent successfully',
            'data' => $responseMessage->json('data')
        ]);

    $this->assertDatabaseHas('chat_messages', [
        'chat_id' => $response->json('data.id') ,
        'sender_id' => $user1->id ,
        'receiver_id' => $user2->id ,
        'message' => 'first message' ,
        'attachments' => null ,
        'type' => 'message' ,
    ]);

    $id = $response->json('data.id');

    $this->actingAs($user3, 'sanctum');

    $responseGetMessage = $this->getJson("api/chat/$id");

    $responseGetMessage->assertStatus(401);

});

test('user can edit its message', function () {

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
        'user_id' => $user1->id,
        'type' => 'PrivateMember',
    ]);

    $responseMessage = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('api/chat/send-message' , [
        'chat_id' => $response->json('data.id'),
        'receiver_id' => $user2->id ,
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
        'chat_id' => $response->json('data.id') ,
        'sender_id' => $user1->id ,
        'receiver_id' => $user2->id ,
        'message' => 'first message' ,
        'attachments' => null ,
        'type' => 'message' ,
    ]);

    $responseEditMessage = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->putJson('api/chat/edit-message',[
        'id' => $responseMessage->json('data.id'),
        'chat_id' => $response->json('data.id'),
        'message' => 'first message edited' ,
    ]);

    $responseEditMessage->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => [
                'id' => $responseMessage->json('data.id'),
                'chat_id' => $response->json('data.id'),
                'sender_id' => $user1->id,
                'receiver_id' => $user2->id,
                'message' => 'first message edited',
            ],
        ]);

    $this->assertDatabaseHas('chat_messages', [
        'chat_id' => $response->json('data.id') ,
        'message' => 'first message edited' ,
    ]);

});

test('user cant edit someone else message', function () {

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

    $token2 = $user2->createToken('test-token')->plainTextToken;

    $response = $this
        ->withHeader('Authorization', 'Bearer ' . $token2)
        ->postJson('api/chat/create/'. json_encode($user1->id), [
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
            'members' => [$user2->id , $user1->id]
        ]);

    $this->assertDatabaseHas('chats', [
        'id' => $response->json('data.id'),
        'type' => 'private',
    ]);

    $this->assertDatabaseHas('chat_members', [
        'chat_id' => $response->json('data.id'),
        'user_id' => $user1->id,
        'type' => 'PrivateMember',
    ]);

    $responseMessage = $this
        ->withHeader('Authorization', 'Bearer ' . $token2)
        ->postJson('api/chat/send-message' , [
        'chat_id' => $response->json('data.id'),
        'receiver_id' => $user1->id ,
        'message' => 'user2 message to user1' ,
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
        'chat_id' => $response->json('data.id') ,
        'sender_id' => $user2->id ,
        'receiver_id' => $user1->id ,
        'message' => 'user2 message to user1' ,
        'attachments' => null ,
        'type' => 'message' ,
    ]);

    $token1 = $user1->createToken('test-token')->plainTextToken;
    $this->actingAs($user1,'sanctum');

    $responseEditMessage = $this
        ->withHeader('Authorization', 'Bearer ' . $token1)
        ->putJson('api/chat/edit-message',[
        'id' => $responseMessage->json('data.id'),
        'chat_id' => $response->json('data.id'),
        'message' => 'trying to edit message user 2' ,
    ]);

    $responseEditMessage->assertStatus(404);

    $this->assertDatabaseMissing('chat_messages', [
        'id' => $responseMessage->json('data.id'),
        'chat_id' => $response->json('data.id') ,
        'message' => 'trying to edit message user 2' ,
    ]);
});

test('user can delete its message', function () {

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
        'user_id' => $user1->id,
        'type' => 'PrivateMember',
    ]);

    $responseMessage = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('api/chat/send-message' , [
        'chat_id' => $response->json('data.id'),
        'receiver_id' => $user2->id ,
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
        'chat_id' => $response->json('data.id') ,
        'sender_id' => $user1->id ,
        'receiver_id' => $user2->id ,
        'message' => 'first message' ,
        'attachments' => null ,
        'type' => 'message' ,
    ]);

    $responseDeleteMessage = $this->deleteJson('api/chat/delete-message',[
        'user_id' => $user1->id,
        'id' => $responseMessage->json('data.id'),
        'chat_id' => $response->json('data.id'),
    ]);

    $responseDeleteMessage->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'message deleted successfully',
        ]);

    $this->assertDatabaseMissing('chat_messages', [
        'id' => $responseMessage->json('data.id'),
        'chat_id' => $response->json('data.id') ,
        'message' => 'first message' ,
    ]);

});

test('user cant delete someone else message', function () {

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

    $token2 = $user2->createToken('test-token')->plainTextToken;

    $response = $this
        ->withHeader('Authorization', 'Bearer ' . $token2)
        ->postJson('api/chat/create/'. json_encode($user1->id), [
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
            'members' => [$user2->id , $user1->id]
        ]);

    $this->assertDatabaseHas('chats', [
        'id' => $response->json('data.id'),
        'type' => 'private',
    ]);

    $this->assertDatabaseHas('chat_members', [
        'chat_id' => $response->json('data.id'),
        'user_id' => $user1->id,
        'type' => 'PrivateMember',
    ]);

    $responseMessage = $this
        ->withHeader('Authorization', 'Bearer ' . $token2)
        ->postJson('api/chat/send-message' , [
        'chat_id' => $response->json('data.id'),
        'receiver_id' => $user1->id ,
        'message' => 'user2 message' ,
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
        'chat_id' => $response->json('data.id') ,
        'sender_id' => $user2->id ,
        'receiver_id' => $user1->id ,
        'message' => 'user2 message' ,
        'attachments' => null ,
        'type' => 'message' ,
    ]);

    $token1 = $user1->createToken('test-token')->plainTextToken;
    $this->actingAs($user1,'sanctum');

    $responseDeleteMessage = $this
        ->withHeader('Authorization', 'Bearer ' . $token1)
        ->deleteJson('api/chat/delete-message',[
        'id' => $responseMessage->json('data.id'),
        'chat_id' => $response->json('data.id'),
    ]);

    $responseDeleteMessage->assertStatus(404);

    $this->assertDatabaseHas('chat_messages', [
        'id' => $responseMessage->json('data.id'),
        'chat_id' => $response->json('data.id') ,
        'message' => 'user2 message' ,
    ]);

});
