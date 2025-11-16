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

    protected function product_paginate($data){
        return [
            'data' => $data->items(),
            'pagination' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'first_page_url' => $data->url(1),
                'last_page_url' => $data->url($data->lastPage()),
                'next_page_url' => $data->nextPageUrl(),
                'prev_page_url' => $data->previousPageUrl(),
            ]
        ];
    }
}
