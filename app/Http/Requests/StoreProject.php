<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use App\Rules\ValidProvider;
use Illuminate\Foundation\Http\FormRequest;

class StoreProject extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request)
    {
        return [
            'name' => ['required', 'regex:/^[a-zA-Z0-9-_]*$/i'],
            'provider' => [new ValidProvider]
        ];
    }
}