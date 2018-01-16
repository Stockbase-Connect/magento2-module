<?php

namespace Stockbase\Integration\Model\ResourceModel;

/**
 * Class StockItem
 * @package Stockbase\Integration\Model\ResourceModel
 */
class ProductImage extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * @var \Magento\Framework\EntityManager\EntityManager
     */
    protected $entityManager;

    /**
     * Constructor.
     */
    protected function _construct()
    {
        $this->_init('stockbase_product_images', 'id');
    }

    /**
     * @param $imageName
     * @param $productId
     * @param $ean
     * @return mixed
     */
    public function imageExists($imageName, $productId, $ean)
    {
        $connection = $this->getConnection();
        $query = $connection->select()
            ->from(['s' => $this->getMainTable()], ['s.ean'])
            ->where('s.image = ? AND s.product_id = ? AND s.ean= ?', $imageName, $productId, $ean);
        $eans = $connection->fetchAll($query);
        return $eans;
    }

    /**
     * @return mixed
     */
    public function getProcessedEans()
    {
        $connection = $this->getConnection();
        $query = $connection->select()
            ->from(['s' => $this->getMainTable()], ['s.ean'])
            ->group('s.ean');
        $eans = $connection->fetchAll($query);
        return $eans;
    }

}
