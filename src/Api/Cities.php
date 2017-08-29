<?php

namespace Gentor\Olx\Api;


/**
 * Class Cities
 * @package Gentor\Olx\Api
 */
class Cities
{
    /** @var Client $client */
    private $client;

    /**
     * Cities constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function get($page = 1, $limit = 500)
    {
        $response = $this->client->request('GET', 'open/cities', [
            'page' => $page,
            'limit' => $limit
        ]);

        return !empty($response->results) ? $response->results : [];
    }
}