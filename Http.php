<?php

namespace app\components\bot;

use Yii;
use yii\httpclient\Client;

/**
 * Class Http
 * @package app\bot\http
 */
class Http
{
    private $client;
    private $headers = [];

    /**
     * Http constructor.
     */
    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * @param $name
     * @param $value
     */
    public function addHeader($name, $value)
    {
        $this->headers[] = "{$name}: {$value}";
    }

    /**
     * Clear headers.
     */
    public function clearHeaders()
    {
        $this->headers = [];
    }

    /**
     * @param string $token
     * @param $type
     */
    public function addAuthHeader($type, $token)
    {
        $this->headers[] = 'Authorization: ' . $type . ' ' . $token;
    }

    /**
     * @param $url
     * @param bool $raw
     * @param array $params
     * @return \yii\httpclient\Response
     */
    public function request($url, $params, $raw = false)
    {
        $request = $this->client->createRequest()
            ->setUrl($url)
            ->setMethod('post')
            ->setHeaders($this->headers);
        if ($raw === true) {
            $request->setContent(json_encode($params))
                ->addHeaders([
                    'content-type' => 'application/json'
                ]);
        } else {
            $request->setData($params);
        }
        $response = $request->send();
        if ($response->statusCode !== '200' &&
            $response->statusCode !== '201' &&
            $response->statusCode !== '202'
        ) {
            $this->errorLog($request, $response);
        }
        Yii::error($params, 'clientResponse');

        return $response;
    }

    /**
     * @param \yii\httpclient\Request $request
     * @param \yii\httpclient\Response $response
     */
    private function errorLog($request, $response)
    {
        $errorParams = [
            'url' => $request->getUrl(),
            'responseHeaders' => $response->getHeaders(),
            'data' => $response->getData(),
            'http_code' => $response->getStatusCode(),
            'content' => $request->getContent(),
            'params' => $request->data,
            'requestHeaders' => $request->getHeaders(),
        ];
        Yii::error($errorParams, 'botErrors');
    }
}
