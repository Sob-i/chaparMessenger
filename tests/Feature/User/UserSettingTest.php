<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
uses(RefreshDatabase::class);

    /**
     * A basic test example.
     */

test('user can block another user with valid info', function () {

    $user1 = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' =>Hash::make('Password1234!'),
    ]);

    $user2 = User::factory()->create([
        'name' => 'John Doe Enemy',
        'email' => 'imhisenemy@example.com',
        'password' =>Hash::make('Password1234!'),
    ]);

    $token = $user1->createToken('test-token')->plainTextToken;

    $response = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('api/user-setting/block-users',[
            'blocked_id' => [$user2->id]
        ]);

    $response->assertStatus(201)->
    assertJson([
        'success' => true,
        'message' => 'User blocked',
    ]);

    $this->assertDatabaseHas('blocked_users', [
        'user_id' => $user1->id,
        'blocked_id' => $user2->id
    ]);

});

test('user cant block another user with invalid info', function () {

    $user1 = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' =>Hash::make('Password1234!'),
    ]);

    $user2 = User::factory()->create([
        'name' => 'John Doe Enemy',
        'email' => 'imhisenemy@example.com',
        'password' =>Hash::make('Password1234!'),
    ]);

    $token = $user1->createToken('test-token')->plainTextToken;

    $response = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('api/user-setting/block-users');

    $response->assertStatus(422);

    $this->assertDatabaseMissing('blocked_users', [
        'user_id' => $user1->id,
        'blocked_id' => $user2->id
    ]);

});

test('user can get its blocked users', function () {

    $user1 = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' =>Hash::make('Password1234!'),
    ]);

    $user2 = User::factory()->create([
        'name' => 'John Doe Enemy',
        'email' => 'imhisenemy@example.com',
        'password' =>Hash::make('Password1234!'),
    ]);

    $user3 = User::factory()->create([
        'name' => 'John Doe Blood Thirsty Enemy',
        'email' => 'imgonnaeathim@example.com',
        'password' =>Hash::make('Password1234!'),
    ]);

    $token = $user1->createToken('test-token')->plainTextToken;

    $response = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('api/user-setting/block-users',[
            'blocked_id' => [$user2->id , $user3->id]
        ]);

    $response->assertStatus(201)->
    assertJson([
        'success' => true,
        'message' => 'User blocked',
    ]);

    $this->assertDatabaseHas('blocked_users', [
        'user_id' => $user1->id,
        'blocked_id' => $user2->id
    ]);

    $this->assertDatabaseHas('blocked_users', [
        'user_id' => $user1->id,
        'blocked_id' => $user3->id
    ]);

    $getBlockedResponse = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson('api/user-setting/blocked-users');

    $getBlockedResponse->assertStatus(200)->
    assertJson([
        'success' => true,
        'blockedUsers' => $getBlockedResponse->json('blockedUsers'),
    ]);
});

test('user can unblock blocked users', function () {

    $user1 = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' =>Hash::make('Password1234!'),
    ]);

    $user2 = User::factory()->create([
        'name' => 'John Doe Enemy',
        'email' => 'imhisenemy@example.com',
        'password' =>Hash::make('Password1234!'),
    ]);

    $user3 = User::factory()->create([
        'name' => 'John Doe Blood Thirsty Enemy',
        'email' => 'imgonnaeathim@example.com',
        'password' =>Hash::make('Password1234!'),
    ]);

    $token = $user1->createToken('test-token')->plainTextToken;

    $response = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('api/user-setting/block-users',[
            'blocked_id' => [$user2->id , $user3->id]
        ]);

    $response->assertStatus(201)->
    assertJson([
        'success' => true,
        'message' => 'User blocked',
    ]);

    $this->assertDatabaseHas('blocked_users', [
        'user_id' => $user1->id,
        'blocked_id' => $user2->id
    ]);

    $this->assertDatabaseHas('blocked_users', [
        'user_id' => $user1->id,
        'blocked_id' => $user3->id
    ]);

    $unBlockedResponse = $this
        ->withHeader('Authorization', 'Bearer ' . $token)
        ->deleteJson('api/user-setting/unblock-users', [
                'blocked_id' => [$user2->id , $user3->id]
            ]);

    $unBlockedResponse->assertStatus(200)->
        assertJson([
            'success' => true,
            'message' => 'User Unblocked',
    ]);

    $this->assertDatabaseMissing('blocked_users', [
        'user_id' => $user1->id ,
        'blocked_id' => $user2->id
    ]);

    $this->assertDatabaseMissing('blocked_users', [
        'user_id' => $user1->id ,
        'blocked_id' => $user3->id
    ]);
});

test('blocked user cant messages the user who is blocked by', function () {

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
        ->postJson('api/user-setting/block-users',[
            'blocked_id' => [$user2->id]
        ]);

    $response->assertStatus(201)->
    assertJson([
        'success' => true,
        'message' => 'User blocked',
    ]);

    $this->assertDatabaseHas('blocked_users', [
        'user_id' => $user1->id,
        'blocked_id' => $user2->id
    ]);

    $token2 = $user2->createToken('test-token')->plainTextToken;
    $this->actingAs($user2);

    $response = $this
        ->withHeader('Authorization', 'Bearer ' . $token2)
        ->postJson('api/chat/create/'. json_encode($user1->id), [
            'type' => 'private',
        ]);

    $responseMessage = $this
        ->withHeader('Authorization', 'Bearer ' . $token2)
        ->postJson('api/chat/send-message' , [
            'chat_id' => $response->json('data.id'),
            'receiver_id' => $user1->id ,
            'message' => 'first message' ,
            'attachments' => null ,
            'type' => 'message' ,
        ]);

    $responseMessage->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'you cant send message to this person',
        ]);

    $this->assertDatabaseMissing('chat_messages', [
        'chat_id' => $response->json('data.id') ,
        'sender_id' => $user2->id ,
        'receiver_id' => $user1->id ,
        'message' => 'first message' ,
        'attachments' => null ,
        'type' => 'message' ,
    ]);
});
