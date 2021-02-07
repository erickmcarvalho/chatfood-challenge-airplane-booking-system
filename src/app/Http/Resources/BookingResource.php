<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'passenger' => $this['name'],
            'flight' => (new FlightResource($this['flight']))->withAirplaneInfo(),
            'booking' => $this['booking'],
            'seats' => $this->when($this['seats'] instanceof Collection, new BookingSeatCollection($this['seats']), $this['seats'])
        ];
    }
}
