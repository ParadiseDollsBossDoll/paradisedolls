<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreCommunityMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'message' => ['nullable', 'string', 'max:4000', 'required_without:attachment'],
            'reply_to' => ['nullable', 'integer', 'exists:community_messages,id'],
            'attachment' => [
                'nullable',
                File::types(['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip'])
                    ->max(10 * 1024),
            ],
        ];
    }
}
