<?php


namespace Stockbase\Integration\Controller\Adminhtml\System\Config;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Eav\Api\AttributeManagementInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeSetCollectionFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;

/**
 * CreateEanAttribute controller.
 */
class CreateEanAttribute extends Action
{
    /**
     * @var AttributeFactory
     */
    private $attributeFactory;
    /**
     * @var AttributeManagementInterface
     */
    private $attributeManagement;
    /**
     * @var AttributeSetCollectionFactory
     */
    private $attributeSetCollectionFactory;

    /**
     * @param Context                       $context
     * @param AttributeFactory              $attributeFactory
     * @param AttributeManagementInterface  $attributeManagement
     * @param AttributeSetCollectionFactory $attributeSetCollectionFactory
     */
    public function __construct(
        Context $context,
        AttributeFactory $attributeFactory,
        AttributeManagementInterface $attributeManagement,
        AttributeSetCollectionFactory $attributeSetCollectionFactory
    ) {
        parent::__construct($context);
        $this->attributeFactory = $attributeFactory;
        $this->attributeManagement = $attributeManagement;
        $this->attributeSetCollectionFactory = $attributeSetCollectionFactory;
    }
    

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $response */
        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        
        $request = $this->getRequest();
        if (!$request instanceof Http || !$request->isPost()) {
            $response->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST);
            $response->setData(['message' => __('Invalid request.')]);

            return $response;
        }
        
        $result = [];

        $entityTypeId = $this->_objectManager->create(\Magento\Eav\Model\Entity::class)
            ->setType(ProductAttributeInterface::ENTITY_TYPE_CODE)
            ->getTypeId();

        $attribute = $this->attributeFactory->create();
        $attribute->loadByCode(ProductAttributeInterface::ENTITY_TYPE_CODE, 'ean');
        
        if ($attribute->isEmpty()) {
            // Create the attribute
            $attribute->setEntityTypeId($entityTypeId);
            $attribute->setAttributeCode('ean');
            $attribute->setDefaultFrontendLabel('EAN');
            $attribute->setBackendType('varchar');
            $attribute->setFrontendInput('text');
            $attribute->setFrontendClass('validate-digits');
            $attribute->setIsUserDefined(true);
            $attribute->setIsGlobal(ScopedAttributeInterface::SCOPE_GLOBAL);
            $attribute->setIsVisibleOnFront(false);
            $attribute->setIsRequired(false);
            $attribute->setIsUnique(false);
            $attribute->setData(Attribute::IS_USED_IN_GRID, false);
            $attribute->setApplyTo(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);

            $attribute->save();

            // Assign the attribute to every attribute set
            $attributeSetCollection = $this->attributeSetCollectionFactory->create();
            $attributeSetCollection->setEntityTypeFilter($entityTypeId);
            foreach ($attributeSetCollection->getItems() as $attributeSet) {
                $this->attributeManagement->assign(
                    ProductAttributeInterface::ENTITY_TYPE_CODE,
                    $attributeSet->getAttributeSetId(),
                    $attributeSet->getDefaultGroupId(),
                    $attribute->getAttributeCode(),
                    0
                );
            }
        }

        $result['eanField'] = [
            'label' => $attribute->getDefaultFrontendLabel(),
            'value' => $attribute->getAttributeCode(),
        ];
        $response->setData($result);
        
        return $response;
    }
}
