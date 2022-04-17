<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function setResponse(int|string $status, mixed $data, string $message = '')
    {
        return [
            'status' => $status,
            'data' => $data,
            'message' => $message
        ];
    }

    protected function sendResponse(int|string $status, mixed $data, string $message = '')
    {
        return response($this->setResponse($status, $data, $message));
    }

    protected function sendOkResponse(mixed $data, string $message = '')
    {
        return response($this->setResponse(200, $data, $message));
    }

    protected function sendBadResponse(mixed $data, string $message = '')
    {
        return response($this->setResponse(400, $data, $message));
    }

    protected function sendErrorResponse(mixed $data, string $message = '')
    {
        return response($this->setResponse(500, $data, $message));
    }
}
