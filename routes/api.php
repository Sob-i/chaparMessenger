<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Chat\ChatsController;
use Illuminate\Support\Facades\Route;

// register
    Route::post('api/register', [AuthController::class, 'register']);

    // login
    Route::post('api/login', [AuthController::class, 'login']);

Route::prefix('')->middleware('auth:sanctum')->group(function () {

    // logout
    Route::post('api/logout', [AuthController::class, 'logout']);

    // chats
    Route::get('api/chats', [ChatsController::class, 'chat']);
    Route::post('api/chat/create/{membersId}', [ChatsController::class, 'createChat']);

    // chat messages
    Route::get('api/chat/{id}', [ChatsController::class, 'chat.show.message']);
    Route::post('api/chat/{id}/send-message', [ChatsController::class, 'chat.send.message']);
    Route::put('api/chat/{id}/edit-message/{messageId}', [ChatsController::class, 'chat.edit.message']);
    Route::delete('api/chat/{id}/delete-message/{messageId}', [ChatsController::class, 'chat.delete.message']);
});
