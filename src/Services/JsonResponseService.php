<?php

namespace Empress\Services;

class JsonResponseService
{
    /** @var \Empress\Services\ResponseService */
    private $responseService;

    public function __construct(ResponseService $responseService)
    {
        $this->responseService = $responseService;
    }

    public function with(array $payload)
    {
        $headers = [
            'content-type' => 'application/json; charset=utf-8'
        ];

        return $this->responseService->with(json_encode($payload), $headers);
    }
}
