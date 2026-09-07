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

    $response = $this->postJson('api/chat/create/'. json_encode([$user1->id , $user2->id]), [
        'type' => 'private',
    ]);

    $response->assertStatus(201)
    ->assertJson([
        'success' => true,
        'message' => 'chat created successfully',
        'data' => [
            'id' => '1',
            'type' => 'private',
            'name' => null,
        ],
        'members' => [$user1->id , $user2->id]
    ]);

    $this->assertDatabaseHas('chats', [
        'id' => '1',
        'type' => 'private',
    ]);

    $this->assertDatabaseHas('chat_members', [
        'chat_id' => '1',
        'user_id' => $user2->id,
    ]);
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
            'id' => '2',
            'type' => 'group',
            'name' => 'group chat 1',
        ],
        'members' => [$user1->id , $user2->id , $user3->id]
    ]);

    $this->assertDatabaseHas('chats', [
        'id' => '2',
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
                'id' => '3',
                'type' => 'channel',
                'name' => 'mmd',
            ],
            'members' => [$user1->id]
        ]);

    $this->assertDatabaseHas('chats', [
        'id' => '3',
        'type' => 'channel',
    ]);
    $this->assertDatabaseHas('channel_members', [
        'chat_id' => '3',
        'user_id' => '6',
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
