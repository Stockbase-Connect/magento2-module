<?php


namespace Strategery\Stockbase\Api\Client;

use Assert\Assertion;
use DivideBV\PHPDivideIQ\DivideIQ;

class StockbaseClient
{
    const STOCKBASE_STOCK_ENDPOINT = 'stockbase_stock';
    const STOCKBASE_IMAGES_ENDPOINT = 'stockbase_images';
    
    /**
     * @var DivideIQ
     */
    private $divideIqClient;

    public function __construct(DivideIQ $divideIqClient)
    {
        $this->divideIqClient = $divideIqClient;
    }

    /**
     * @param \DateTime|null $since
     * @param \DateTime|null $until
     * @return object
     * @throws \Exception
     */
    public function getStock(\DateTime $since = null, \DateTime $until = null)
    {
        $data = [];
        if ($since !== null) {
            $data['Since'] = $since->getTimestamp();
        }
        if ($until !== null) {
            $data['Until'] = $until->getTimestamp();
        }
        return $this->divideIqClient->request(self::STOCKBASE_STOCK_ENDPOINT, $data);
    }

    /**
     * @param string[] $eans
     * @return object
     * @throws \Exception
     */
    public function getImages(array $eans)
    {
        Assertion::allNumeric($eans);
        
        $data = [
            'ean' => implode(',', $eans),
        ];
        return $this->divideIqClient->request(self::STOCKBASE_IMAGES_ENDPOINT, $data);
    }
}
