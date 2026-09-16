<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'chat_id' => 'required|integer|exists:chats,id',
            'receiver_id' => 'required|integer|exists:users,id',
            'message' => 'nullable|string|min:1',
            'attachments' => 'nullable|url',
            'type' => 'required|string|in:message,reply',
            'reply_to_message' => 'nullable|integer|exists:chat_messages,id',
            'reply_to_user' => 'nullable|integer|exists:users,id',
        ];
    }
}
