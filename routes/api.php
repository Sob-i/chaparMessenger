<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Chat\ChatsController;
use App\Http\Controllers\Api\V1\Chat\ChatMessageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\User\UserProfileController;
use App\Http\Controllers\Api\V1\User\UserSettingController;
use App\Http\Controllers\Api\V1\Search\SearchController;

    // register
    Route::post('api/register', [AuthController::class, 'register']);

    // login
    Route::post('api/login', [AuthController::class, 'login']);

Route::prefix('')->middleware('auth:sanctum')->group(function () {

    // logout
    Route::post('api/logout', [AuthController::class, 'logout']);

    // search
    Route::get('api/search', [SearchController::class, 'searchChats']);
    Route::get('api/chat/{id}/search', [SearchController::class, 'searchChatMessages']);

    // chats
    Route::get('api/chats', [ChatsController::class, 'getChats']);
    Route::post('api/chat/create/{membersId}', [ChatsController::class, 'createChat']);

    // chat messages
    Route::get('api/chat/{id}', [ChatMessageController::class, 'getChatMessages']);
    Route::post('api/chat/send-message', [ChatMessageController::class, 'sendMessage']);
    Route::put('api/chat/edit-message', [ChatMessageController::class, 'editMessage']);
    Route::delete('api/chat/delete-message', [ChatMessageController::class, 'deleteMessage']);

    // user profile
    Route::get('api/user-profile', [UserProfileController::class, 'getProfile']);
    Route::put('api/user-profile/edit', [UserProfileController::class, 'editProfile']);

    // user setting
    Route::get('api/user-setting/blocked-users', [UserSettingController::class, 'getBlockedUsers']);
    Route::post('api/user-setting/block-users', [UserSettingController::class, 'blockUser']);
    Route::delete('api/user-setting/unblock-users', [UserSettingController::class, 'unblockUser']);
});
