<?php

namespace Empress\Services;

class JsonResponseService extends AbstractService
{
    public function getServiceObject()
    {
        return $this;
    }
    public function with(array $payload)
    {
        $headers = [
            'content-type' => 'application/json; charset=utf-8'
        ];

        return $this->container['response']->with(json_encode($payload), $headers);
    }
}
