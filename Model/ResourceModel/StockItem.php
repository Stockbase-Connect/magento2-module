<?php


namespace Strategery\Stockbase\Model\ResourceModel;


class StockItem extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\EntityManager\EntityManager
     */
    protected $entityManager;

    protected function _construct()
    {
        $this->_init('stockbase_stock', 'ean');
    }

    public function bulkUpdate(array $data)
    {
        $connection = $this->getConnection();
        
        $connection->beginTransaction();
        try {
            $connection->insertOnDuplicate($this->getMainTable(), $data);
            
            $connection->commit();
        }
        catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }
}
