<?php

namespace app\components\bot;

/**
 * Class Auth
 * @package app\bot\auth
 */
class Auth
{
    const AUTH_URL = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';
    private $http;
    private $params;

    /**
     * Auth constructor.
     * @param $client
     * @param $secret
     * @param Http $http
     */
    public function __construct($client, $secret, Http $http)
    {
        $this->http = $http;
        $this->params = [
            'grant_type' => 'client_credentials',
            'client_id' => $client,
            'client_secret' => $secret,
            'scope' => 'https://graph.microsoft.com/.default',
        ];
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getToken()
    {
        $response = $this->http->request(self::AUTH_URL, $this->params);
        if ('200' === $response->statusCode) {
            return $response->data;
        }
        throw new \Exception('Error getting the access token!');
    }
}
