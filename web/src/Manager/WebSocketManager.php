<?php

namespace App\Manager;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class WebSocketManager.
 */
class WebSocketManager
{
    /**
     * @var ParameterBagInterface
     */
    private $params;

    /**
     * @var HttpClientInterface
     */
    private $client;

    public function __construct(
        ParameterBagInterface $params,
        HttpClientInterface $client
    ) {
        $this->params = $params;
        $this->client = $client;
    }

    public function send(string $channel, array $data): bool
    {
        $url = $this->params->get('app.ws.server_url');
        $serverToken = $this->params->get('app.ws.server_token');

        $url .= '/messages?server_token=' . $serverToken . '&channel=' . $channel;

        try {
            $response = $this->client->request(
                'POST',
                $url,
                [
                    'body' => json_encode($data),
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                ]
            );

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
