<?php

namespace App\Http\Requests;

use App\Models\CommunityChannel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCommunityChannelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canModerateCommunity() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:60'],
            'is_private' => ['nullable', 'boolean'],
            'access_mode' => ['required', Rule::in(CommunityChannel::accessModes())],
            'denied_behavior' => ['required', Rule::in(CommunityChannel::deniedBehaviors())],
            'allowed_roles' => ['nullable', 'array'],
            'allowed_roles.*' => ['string', Rule::in(['admin', 'moderator', 'model'])],
            'invited_user_ids' => ['nullable', 'array'],
            'invited_user_ids.*' => ['integer', 'exists:users,id'],
            'is_locked' => ['nullable', 'boolean'],
            'slowmode_seconds' => ['nullable', 'integer', 'min:0', 'max:300'],
        ];
    }
}
