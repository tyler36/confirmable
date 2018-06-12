<?php

namespace App\Http\Requests;

use App\User;
use Illuminate\Foundation\Http\FormRequest;

class ConfirmationFormRequest extends FormRequest
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
    public function rules()
    {
        $rules =  [
            'email'      => ['required', 'exists:confirmations,email'],
            'terms'      => ['accepted'],
            'token'      => ['required'],
        ];

        return $rules;
    }

    /**
     * Override default messages
     *
     * @return void
     */
    public function messages()
    {
        return [
            'terms.accepted' => trans('validation.accepted', ['attribute' => trans('confirmable::message.terms')]),
            'token.required' => trans('validation.required', ['attribute' => trans('confirmable::message.token')]),
        ];
    }
}
