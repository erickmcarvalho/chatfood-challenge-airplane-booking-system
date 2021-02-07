<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class BookingFlightCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->map(function ($model) {
            return [
                'id' => $model->id,
                'name' => $model->name,
                'source' => $model->source,
                'destination' => $model->destination,
                'flightDate' => $model->flight_date->format("Y-m-d\TH:i:sP"),
                'airplane' => new FlightAirplaneResource($model->airplane)
            ];
        })->toArray();
    }
}
