<?php

namespace App\Http\Requests\System\Flight;

use Illuminate\Foundation\Http\FormRequest;

class CreateFlightRequest extends FormRequest
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
            'name' => ['required', 'max:255'],
            'source' => ['required', 'max:255'],
            'destination' => ['required', 'max:255'],
            'flightDate' => ['required', 'date_format:Y-m-d\TH:i:sP'],
            'airplaneId' => ['required', 'exists:airplanes,id']
        ];
    }
}
