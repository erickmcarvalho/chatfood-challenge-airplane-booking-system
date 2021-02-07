<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FlightResource extends JsonResource
{
    /**
     * If the airplane id that can returned.
     *
     * @var bool
     */
    private bool $showAirplaneId = false;

    /**
     * Set the airplane id returnable.
     *
     * @returns $this
     */
    public function withAirplaneId(): static
    {
        $this->showAirplaneId = true;
        return $this;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'source' => $this->source,
            'destination' => $this->destination,
            'flightDate' => $this->flight_date->format("Y-m-d\TH:i:sP"),
            'airplaneId' => $this->when($this->showAirplaneId === true, $this->airplane_id)
        ];
    }
}
