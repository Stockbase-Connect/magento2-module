<?php


namespace Stockbase\Integration\StockbaseApi\Client\DivideIQ;

use DivideBV\PHPDivideIQ\DivideIQ;

/**
 * DivideIQ API client with extended functionality.
 */
class DivideIQClient extends DivideIQ
{
    /**
     * {@inheritdoc}
     */
    public function __construct($username, $password, $environment = 'production', array $clientOptions = [])
    {
        parent::__construct($username, $password, $environment, $clientOptions);
    }
}
