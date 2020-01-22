<?php

namespace Gentor\Olx\Api;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;
use function GuzzleHttp\Psr7\stream_for;

/**
 * Class Client
 * @package Gentor\Olx\Api
 */
class Client
{
    /**
     *
     */
    const OLX_PL = 1;
    /**
     *
     */
    const OLX_BG = 2;
    /**
     *
     */
    const OLX_RO = 3;
    /**
     *
     */
    const OLX_UA = 4;
    /**
     *
     */
    const OLX_KZ = 5;
    /**
     *
     */
    const OLX_BY = 6;
    /**
     *
     */
    const OLX_PT = 7;
    /**
     *
     */
    const OLX_AO = 8;
    /**
     *
     */
    const OLX_MZ = 9;

    /**
     * @var GuzzleClient
     */
    protected $client;
    /**
     * @var
     */
    protected $token;
    /**
     * @var
     */
    protected $country;
    /**
     * @var array
     */
    protected $credentials;

    /** @var Cities $cities */
    public $cities;

    /** @var Categories $categories */
    public $categories;

    /** @var Adverts $adverts */
    public $adverts;

    /**
     * Client constructor.
     * @param array $credentials
     * @param $country_id
     */
    public function __construct(array $credentials, $country_id)
    {
        $this->credentials = [
            'client_id' => $credentials['client_id'],
            'client_secret' => $credentials['client_secret'],
            'partner_code' => $credentials['partner_code'],
            'partner_secret' => $credentials['partner_secret'],
            'username' => $credentials['username'],
            'password' => $credentials['password'],
        ];

        $this->country = $country_id;

        $this->client = new GuzzleClient([
            'base_uri' => $this->getBaseUrl(),
            'headers' => $this->setHeaders(false)
        ]);

        $this->cities = new Cities($this);
        $this->categories = new Categories($this);
        $this->adverts = new Adverts($this);
    }

    /**
     * @return object
     * @throws OlxException
     */
    public function generateToken()
    {
        try {
            /** @var Response $response */
            $response = $this->client->post('open/oauth/token',
                [
                    'json' => [
                        'grant_type' => 'password',
                        'username' => $this->credentials['username'],
                        'password' => $this->credentials['password'],
                        'scope' => 'read write',
                        'client_id' => $this->credentials['client_id'],
                        'client_secret' => $this->credentials['client_secret']
                    ]
                ]);
        } catch (ClientException $e) {
            $this->handleException($e);
            return [];
        }

        if (!$token = $this->handleResponse($response)) {
            // Handle stupid HTML error with response 200 OK
            $html = $response->getBody() . '<style>.container {width: auto !important;}</style>';
            throw new OlxException($html, 0, new \stdClass());
        }

        $this->token = $token->access_token;

        return $token;
    }

    /**
     * @param $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @param bool $includeToken
     * @return array
     */
    protected function setHeaders($includeToken = true)
    {
        $headers = [
            'Accept' => 'application/json'
        ];

        if ($includeToken) {
            if (!$this->token) {
                $this->generateToken();
            }
            $headers['Authorization'] = 'Bearer ' . $this->token;
        }

        return $headers;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        switch ($this->country) {
            case static::OLX_PL: // Poland
                return 'https://www.olx.pl/api/';
            case static::OLX_BG: // Bulgaria
                return 'https://www.olx.bg/api/';
            case static::OLX_RO: // Romania
                return 'https://www.olx.ro/api/';
            case static::OLX_UA: // Ukraine
                return 'https://www.olx.ua/api/';
            case static::OLX_KZ: // Kazakhstan
                return 'https://www.olx.kz/api/';
            case static::OLX_BY: // Belarus
                return 'https://www.olx.by/api/';
            case static::OLX_PT: // Portugal
                return 'https://www.olx.pt/api/';
            case static::OLX_AO: // Angola
                return 'https://www.olx.co.ao/api/';
            case static::OLX_MZ: // Mozambique
                return 'https://www.olx.co.mz/api/';
            default:
                return 'https://www.olx.pl/api/';
        }
    }

    /**
     * @param $method
     * @param $endpoint
     * @param $data
     * @return \stdClass
     * @throws OlxException
     */
    public function request($method, $endpoint, $data = [])
    {
        switch ($method) {
            case 'GET':
                $options = [
                    'query' => $data,
                    'headers' => $this->setHeaders()
                ];
                break;
            case 'DELETE':
                $options = [
                    'headers' => $this->setHeaders()
                ];
                break;
            default:
                $options = [
                    'json' => $data,
                    'headers' => $this->setHeaders()
                ];
        }

        try {
            $response = $this->client->request($method, $endpoint, $options);
        } catch (ClientException $e) {
            $this->handleException($e);
            return [];
        }

        return $this->handleResponse($response);
    }

    /**
     * @param Response $response
     * @return mixed
     */
    private function handleResponse(Response $response)
    {
        $stream = stream_for($response->getBody());
        $data = json_decode($stream, false, 512, JSON_UNESCAPED_UNICODE);

        return $data;
    }

    /**
     * @param ClientException $e
     * @throws OlxException
     */
    private function handleException(ClientException $e)
    {
        $stream = stream_for($e->getResponse()->getBody());
        $details = json_decode($stream, false, 512, JSON_UNESCAPED_UNICODE);

        if (isset($details->error->message)) {
            $message = $details->error->message;
        } elseif (isset($details->error_description)) {
            $message = $details->error_description;
        } else {
            $message = $e->getMessage();
        }

        throw new OlxException($message, $e->getCode(), $details);
    }
}