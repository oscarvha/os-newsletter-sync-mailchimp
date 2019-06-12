<?php

require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class MailChimpIntegration
{
    private $client;
    private $list;
    private $dc;
    private $token;

    public function __construct(string $list, string  $dc, string $token)
    {
        $this->client = new Client();
        $this->list = $list;
        $this->dc = $dc;
        $this->token = $token;
    }

    /**
     * @param string $email
     * @return bool
     * @throws \Predis\ClientException
     */
    public function addSubscriber(string $email)
    {
        try {
          $response =   $this->client->post("https://{$this->dc}.api.mailchimp.com/3.0/lists/$this->list/members", [

              'auth' => ['anything', 'password', $this->token],
              'headers' => [
                    'Authorization' => "Basic {$this->token}",
                    'Content-type' => 'application/json',
                ],
                'json' => [
                    'status' => 'subscribed',
                    'email_address' => $email,
                ],
            ]);

          return true;

        } catch (ClientException $e) {

            if(400 === $e->getCode()) {
                return false;
            }
            throw new \Predis\ClientException('Fatal Error',500);
        }
    }
}