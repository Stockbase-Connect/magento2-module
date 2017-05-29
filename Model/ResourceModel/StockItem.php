<?php


namespace Strategery\Stockbase\Model\ResourceModel;

/**
 * Class StockItem
 */
class StockItem extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const BULK_INSERT_CHUNK_SIZE = 100;
    
    /**
     * @var \Magento\Framework\EntityManager\EntityManager
     */
    protected $entityManager;

    /**
     * Gets the amount of items available on stock.
     *
     * @param string|string[] $eans
     * @return array|string
     */
    public function getNotReservedStockAmount($eans)
    {
        $connection = $this->getConnection();

        $query = $connection->select()
            ->from(
                ['s' => $this->getMainTable()],
                ['s.ean', 'amount' => new \Zend_Db_Expr('IF(s.noos = 1, 1000000000, s.amount - COALESCE(SUM(r.amount), 0))')]
            )
            ->joinLeft(
                ['r' => $this->getTable('stockbase_stock_reserve')],
                'r.ean = s.ean',
                null
            )
            ->where('s.ean in (?)', $eans)
            ->group('s.ean');
        
        $pairs = $connection->fetchPairs($query);

        return is_array($eans) ? $pairs : reset($pairs);
    }

    /**
     * Updates the amount of items available on stock for given EAN.
     *
     * @param string $ean
     * @param float  $amount
     * @param string $operation
     * @return bool
     */
    public function updateStockAmount($ean, $amount, $operation = '-')
    {
        $amount = (float) $amount;
        $operation = $operation == '-' ? '-' : '+';
        
        $connection = $this->getConnection();

        $result = $connection->update(
            $this->getMainTable(),
            ['amount' => new \Zend_Db_Expr(sprintf('amount %s %s', $operation, $amount))],
            ['ean = ?' => $ean]
        );
        if ($result == 1) {
            return true;
        }
        
        return false;
    }

    /**
     * Gets the modification date of the last modified item in the stock.
     *
     * @return \DateTime|null
     */
    public function getLastModifiedItemDate()
    {
        $connection = $this->getConnection();

        $query = $connection->select()
            ->from($this->getMainTable(), 'timestamp')
            ->order('timestamp DESC')
            ->limit(1);

        $result = $connection->fetchCol($query);
        
        return !empty($result[0]) ? new \DateTime($result[0]) : null;
    }

    /**
     * Updates the local stock based on given Stockbase API response.
     *
     * @param \stdClass $stock
     * @return int
     */
    public function updateFromStockObject(\stdClass $stock)
    {
        $data = [];
        $total = 0;
        foreach ($stock->{'Groups'} as $group) {
            foreach ($group->{'Items'} as $item) {
                $total++;
                $data[] = [
                    'ean' => $item->{'EAN'},
                    'brand' => !empty($group->{'Brand'}) ? $group->{'Brand'} : null,
                    'code' => !empty($group->{'Code'}) ? $group->{'Code'} : null,
                    'supplier_code' => !empty($group->{'SupplierCode'}) ? $group->{'SupplierCode'} : null,
                    'supplier_gln' => !empty($group->{'SupplierGLN'}) ? $group->{'SupplierGLN'} : null,
                    'amount' => $item->{'Amount'},
                    'noos' => $item->{'NOOS'}, //TODO: Implement NOOS feature
                    'timestamp' => date('Y-m-d H:i:s', $item->{'Timestamp'}),
                ];
                if (count($data) >= self::BULK_INSERT_CHUNK_SIZE) {
                    $this->bulkUpdate($data);
                    $data = [];
                }
            }
        }
        if (count($data) > 0) {
            $this->bulkUpdate($data);
        }
        
        return $total;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('stockbase_stock', 'ean');
    }

    /**
     * @param array $data
     * @throws \Exception
     */
    protected function bulkUpdate(array $data)
    {
        $connection = $this->getConnection();
        
        $connection->beginTransaction();
        try {
            $connection->insertOnDuplicate($this->getMainTable(), $data);
            
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }
}
