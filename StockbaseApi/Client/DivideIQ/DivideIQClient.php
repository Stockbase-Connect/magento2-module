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
}
