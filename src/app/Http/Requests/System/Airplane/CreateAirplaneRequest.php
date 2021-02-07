<?php

namespace App\Http\Requests\System\Airplane;

use Illuminate\Foundation\Http\FormRequest;

class CreateAirplaneRequest extends FormRequest
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
        return [
            'name' => ['required', 'max:50'],
            'company' => ['required', 'max:50'],
            'seatColumns' => ['required', 'integer', 'min:2'],
            'sitsNumber' => ['required', 'integer', 'min:4', 'multiple_of:'.$this->input('seatColumns')]
        ];
    }
}
