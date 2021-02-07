## CharFood Challenge | Airplace Booking System

This repository contains the application of the challenge proposed by ChatFood.
Challenge: https://gist.github.com/viniciusmr3/7aca567e5c10e1d60d9c2ec16f36f8e9

Thanks for the opportunity!

## Overview

- Airplane registration
- Flight registration
- Booking registration

## Stack

- PHP 8.0
- Laravel 8.26.1
- SQLite

## Architeture

- Repository Pattern
- Service Layer
- TDD

## Endpoints
```+--------+-----------+--------------------------------------------+-----------------------------+---------------------------------------------------------+------------+
| Domain | Method    | URI                                        | Name                        | Action                                                  | Middleware |
+--------+-----------+--------------------------------------------+-----------------------------+---------------------------------------------------------+------------+
|        | GET|HEAD  | /                                          |                             | Closure                                                 | web        |
|        | GET|HEAD  | api/bookings                               | bookings.index              | App\Http\Controllers\BookingController@index            | api        |
|        | POST      | api/bookings                               | bookings.store              | App\Http\Controllers\BookingController@store            | api        |
|        | GET|HEAD  | api/bookings/{booking}                     | bookings.show               | App\Http\Controllers\BookingController@show             | api        |
|        | GET|HEAD  | api/system/airplanes                       | system.airplanes.index      | App\Http\Controllers\System\AirplaneController@index    | api        |
|        | POST      | api/system/airplanes                       | system.airplanes.store      | App\Http\Controllers\System\AirplaneController@store    | api        |
|        | GET|HEAD  | api/system/airplanes/{airplane}            | system.airplanes.show       | App\Http\Controllers\System\AirplaneController@show     | api        |
|        | PUT|PATCH | api/system/airplanes/{airplane}            | system.airplanes.update     | App\Http\Controllers\System\AirplaneController@update   | api        |
|        | DELETE    | api/system/airplanes/{airplane}            | system.airplanes.destroy    | App\Http\Controllers\System\AirplaneController@destroy  | api        |
|        | GET|HEAD  | api/system/airplanes/{airplane}/sits       | system.airplanes.sits.index | App\Http\Controllers\System\AirplaneSitController@index | api        |
|        | GET|HEAD  | api/system/airplanes/{airplane}/sits/{sit} | system.airplanes.sits.show  | App\Http\Controllers\System\AirplaneSitController@show  | api        |
|        | GET|HEAD  | api/system/flights                         | system.flights.index        | App\Http\Controllers\System\FlightController@index      | api        |
|        | POST      | api/system/flights                         | system.flights.store        | App\Http\Controllers\System\FlightController@store      | api        |
|        | GET|HEAD  | api/system/flights/{flight}                | system.flights.show         | App\Http\Controllers\System\FlightController@show       | api        |
|        | PUT|PATCH | api/system/flights/{flight}                | system.flights.update       | App\Http\Controllers\System\FlightController@update     | api        |
|        | DELETE    | api/system/flights/{flight}                | system.flights.destroy      | App\Http\Controllers\System\FlightController@destroy    | api        |
+--------+-----------+--------------------------------------------+-----------------------------+---------------------------------------------------------+------------+
```
## Instalation

1. Composer install
2. Configure `.env` file
3. Run migrations: `php artisan migrate`

## Run tests

- Artisan CLI: `php artisan test`
- GitHub Actions: https://github.com/erickmcarvalho/chatfood-challenge-airplane-booking-system/actions 