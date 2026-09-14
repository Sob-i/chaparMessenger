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

    $this->actingAs($user1);

    $response = $this->postJson('api/chat/create/'. json_encode([$user1->id , $user2->id]), [
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

    $responseMessage = $this->postJson('api/chat/send-message' , [
        'chat_id' => $response->json('data.id'),
        'sender_id' => $user1->id ,
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

    $this->actingAs($user1);

    $response = $this->postJson('api/chat/create/'. json_encode([$user1->id , $user2->id]), [
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

    $responseMessage = $this->postJson('api/chat/send-message' , [
        'chat_id' => 1000,
        'sender_id' => 100 ,
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

    $this->actingAs($user1);

    $response = $this->postJson('api/chat/create/' . json_encode([$user1->id, $user2->id]), [
        'type' => 'private',
    ]);

    $response->assertStatus(201);

    $chatId = $response->json('data.id');

    $fileUrl = 'https://example.com/uploads/test-file.jpg';

    $responseMessage = $this->postJson('api/chat/send-message', [
        'chat_id' => $chatId,
        'sender_id' => $user1->id,
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

test('user cant send message using another user id', function () {

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
        'name' => 'moonKnight',
        'email' => 'mark@mk.com',
        'password' =>Hash::make('Password12366784!'),
    ]);

    $this->actingAs($user1);

    $response = $this->postJson('api/chat/create/'. json_encode([$user1->id , $user2->id]), [
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

    $responseMessage = $this->postJson('api/chat/send-message' , [
        'chat_id' => $response->json('data.id'),
        'sender_id' => $user3->id ,
        'receiver_id' => $user2->id ,
        'message' => 'a message from MOOOOOOOOOOOOOOON' ,
        'attachments' => null ,
        'type' => 'message' ,
    ]);

    $responseMessage->assertStatus(201)
        ->assertJsonFragment([
            'chat_id' => $response->json('data.id'),
            'sender_id' => $user1->id,
            'receiver_id' => $user2->id,
            'message' => 'a message from MOOOOOOOOOOOOOOON',
            'type' => 'message',
        ]);

    $this->assertDatabaseMissing('chat_messages', [
        'chat_id' => $response->json('data.id') ,
        'sender_id' => $user3->id ,
        'receiver_id' => $user2->id ,
        'message' => 'a message from MOOOOOOOOOOOOOOON' ,
        'attachments' => null ,
        'type' => 'message' ,
    ]);

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

    $this->actingAs($user1);

    $response = $this->postJson('api/chat/create/'. json_encode([$user1->id , $user2->id]), [
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

    $this->actingAs($user1);

    $responseMessage = $this->postJson('api/chat/send-message' , [
        'chat_id' => $response->json('data.id'),
        'sender_id' => $user1->id ,
        'receiver_id' => $user2->id ,
        'message' => 'first message' ,
        'attachments' => null ,
        'type' => 'message' ,
    ]);

    $this->actingAs($user2);

    $responseMessage2 = $this->postJson('api/chat/send-message' , [
        'chat_id' => $response->json('data.id'),
        'sender_id' => $user2->id ,
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

    $this->actingAs($user1);

    $response = $this->postJson('api/chat/create/'. json_encode([$user1->id , $user2->id]), [
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

    $responseMessage = $this->postJson('api/chat/send-message' , [
        'chat_id' => $response->json('data.id'),
        'sender_id' => $user1->id ,
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

    $responseEditMessage = $this->putJson('api/chat/edit-message',[
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

    $this->actingAs($user2);

    $response = $this->postJson('api/chat/create/'. json_encode([$user1->id , $user2->id]), [
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

    $responseMessage = $this->postJson('api/chat/send-message' , [
        'chat_id' => $response->json('data.id'),
        'sender_id' => $user2->id ,
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

    $this->actingAs($user1);

    $responseEditMessage = $this->putJson('api/chat/edit-message',[
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

    $this->actingAs($user1);

    $response = $this->postJson('api/chat/create/'. json_encode([$user1->id , $user2->id]), [
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

    $responseMessage = $this->postJson('api/chat/send-message' , [
        'chat_id' => $response->json('data.id'),
        'sender_id' => $user1->id ,
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

    $this->actingAs($user2);

    $response = $this->postJson('api/chat/create/'. json_encode([$user1->id , $user2->id]), [
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

    $responseMessage = $this->postJson('api/chat/send-message' , [
        'chat_id' => $response->json('data.id'),
        'sender_id' => $user2->id ,
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

    $this->actingAs($user1);

    $responseDeleteMessage = $this->deleteJson('api/chat/delete-message',[
        'user_id' => $user1->id,
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
