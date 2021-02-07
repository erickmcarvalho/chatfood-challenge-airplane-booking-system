<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Makes a created response.
     *
     * @param mixed $response
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response|object
     */
    public function created(mixed $response)
    {
        if ($response instanceof JsonResource) {
            return $response->toResponse(request())->setStatusCode(201);
        }

        return response($response, 201);
    }

    /**
     * Makes a no content response.
     *
     * @return \Illuminate\Http\Response
     */
    public function noContent()
    {
        return response()->noContent();
    }

    /**
     * Makes a bad request response.
     *
     * @param mixed $response
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    protected function badRequest(mixed $response)
    {
        return response($response, 400);
    }

    /**
     * Makes a not found response.
     *
     * @param mixed $response
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    protected function notFound(mixed $response)
    {
        return response($response, 404);
    }
}
