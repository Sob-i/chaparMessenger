<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Chat\ChatsController;
use App\Http\Controllers\Api\V1\Chat\ChatMessageController;
use App\Http\Controllers\Api\V1\Chat\OneToNChatsSettingsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\User\UserProfileController;
use App\Http\Controllers\Api\V1\User\UserSettingController;
use App\Http\Controllers\Api\V1\Search\SearchController;

    // register
    Route::post('api/register', [AuthController::class, 'register']);

    // login
    Route::post('api/login', [AuthController::class, 'login']);

Route::prefix('')->middleware('auth:sanctum')->group(function () {

    Route::prefix('api/')->middleware('auth:sanctum')->group(function () {
        // logout
        Route::post('logout', [AuthController::class, 'logout']);

        // search
        Route::get('search', [SearchController::class, 'search']);
        Route::get('chat/{id}/search', [SearchController::class, 'searchChatMessages']);

        // chats
        Route::get('chats', [ChatsController::class, 'getChats']);
        Route::post('chat/create/{membersId}', [ChatsController::class, 'createChat']);

        // chat messages
        Route::get('chat/{id}', [ChatMessageController::class, 'getChatMessages']);
        Route::post('chat/send-message', [ChatMessageController::class, 'sendMessage']);
        Route::put('chat/edit-message', [ChatMessageController::class, 'editMessage']);
        Route::delete('chat/delete-message', [ChatMessageController::class, 'deleteMessage']);

        // user profile
        Route::get('user-profile', [UserProfileController::class, 'getProfile']);
        Route::put('user-profile/edit', [UserProfileController::class, 'editProfile']);

        // user setting
        Route::get('user-setting/blocked-users', [UserSettingController::class, 'getBlockedUsers']);
        Route::post('user-setting/block-users', [UserSettingController::class, 'blockUser']);
        Route::delete('user-setting/unblock-users', [UserSettingController::class, 'unblockUser']);

        // one to N chats setting
        Route::get('one-to-n-settings/{id}', [OneToNChatsSettingsController::class, 'getSettings']);
        Route::put('one-to-n-settings/{id}/change-user-type-status', [OneToNChatsSettingsController::class, 'chatAdmin']);
        Route::delete('one-to-n-settings/{id}/ban-user', [OneToNChatsSettingsController::class, 'banMember']);
    });
});
