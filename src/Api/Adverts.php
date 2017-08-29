<?php

namespace Gentor\Olx\Api;


/**
 * Class Adverts
 * @package Gentor\Olx\Api
 */
class Adverts
{
    /** @var Client $client */
    private $client;

    /**
     * Adverts constructor.
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
        $response = $this->client->request('GET', 'open/account/adverts', [
            'page' => $page,
            'limit' => $limit
        ]);

        return !empty($response->results) ? $response->results : [];
    }

    /**
     * @param $id
     * @return \stdClass
     */
    public function find($id)
    {
        return $this->client->request('GET', "open/account/adverts/{$id}");
    }

    /**
     * @param $id
     * @return \stdClass
     */
    public function delete($id)
    {
        return $this->client->request('DELETE', "open/account/adverts/{$id}");
    }
}