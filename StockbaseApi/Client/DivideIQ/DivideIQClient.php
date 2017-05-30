<?php


namespace Strategery\Stockbase\StockbaseApi\Client\DivideIQ;

use DivideBV\PHPDivideIQ\DivideIQ;

/**
 * DivideIQ API client with extended functionality.
 */
class DivideIQClient extends DivideIQ
{
    const DEFAULT_REQUEST_TIMEOUT = 60.0;

    /**
     * {@inheritdoc}
     */
    public function __construct($username, $password, $environment = 'production')
    {
        parent::__construct($username, $password, $environment);
        
        $this->setRequestTimeout(self::DEFAULT_REQUEST_TIMEOUT);
    }

    /**
     * Sets the request timeout value.
     *
     * @param float $timeout Timeout in seconds.
     */
    public function setRequestTimeout($timeout)
    {
        $this->client->setDefaultOption('timeout', $timeout);
    }


    /**
     * {@inheritdoc}
     */
    public function request($serviceName, $payload = [], $method = 'GET')
    {
        // Setup the connection.
        $this->setup();

        $path = $this->settings->getPath($serviceName);

        switch ($method) {
            case 'GET':
                // Perform the request with the payload as a query string.
                $response = $this->client->get($path, [
                    'headers' => ['Authentication' => $this->accessToken->getToken()],
                    'query' => $payload,
                ]);

                break;
            case 'POST':
                $response = $this->client->post($path, [
                    'headers' => ['Authentication' => $this->accessToken->getToken()],
                    'json' => $payload,
                ]);

                break;
        }

        // Parse the response body.
        $body = $response->json(['object' => true])->{'nl.divide.iq'};

        // Check if the settings object is outdated. If so, unset it.
        if ($this->settings->isOutdated($body->{'settings_updated'})) {
            unset($this->settings);
        }

        // Check if there was an error.
        if (!isset($body->response->content)) {
            $message = $body->response->answer;
            $message .= isset($body->response->message) ? ": {$body->response->message}" : '';

            // Throw an exception with the error description from the service.
            throw new \Exception($message);
        }

        // Return only the response content, without the metadata.
        return $body->response->content;
    }

    /**
     * {@inheritdoc}
     */
    protected function setup()
    {
        parent::setup();

        //TODO: Fire the state changed event
    }
}
