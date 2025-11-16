<?php

namespace App;

trait HttpResponses
{
    protected function success($data, string $message = null, int $code = 200)
    {
        return response()->json([
            'status' => 'Request was successful!',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function error($data, string $message = null, int $code)
    {
        return response()->json([
            'status' => 'An error occurred',
            'message' => $message,
            'data' => $data,
        ], $code);
    }
}
