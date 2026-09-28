<?php

namespace App\Http\Controllers\Api\V1\Chat;

use App\Http\Controllers\Controller;
use App\Services\Api\V1\chat\ChatSettingServices;
use Illuminate\Http\Request;

class OneToNChatsSettingsController extends Controller
{
    public function __construct(protected ChatSettingServices $chatSettingServices)
    {

    }

    public function getSettings(Request $request)
    {
        if ($this->chatSettingServices->IsAdmin())
        {

        }
    }
}
