<?php


namespace Stockbase\Integration\StockbaseApi\Client\DivideIQ;

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
     * Downloads a file using current client configuration and saves it at the specified destination.
     * 
     * @param string|\GuzzleHttp\Url $uri File URI.
     * @param string|resource|\GuzzleHttp\Stream\StreamInterface $destination Destination where the file should be saved to.
     */
    public function download($uri, $destination)
    {
        // Setup the connection.
        $this->setup();

        $this->client->get($uri, [
            'headers' => [
                'Content-Type' => null,
                'Authentication' => $this->accessToken->getToken(),
            ],
            'save_to' => $destination, //TODO: Change to `sink` for Guzzle 6
        ]);
    }
}
