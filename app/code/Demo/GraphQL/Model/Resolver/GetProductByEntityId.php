<?php
declare(strict_types=1);

namespace Demo\GraphQL\Model\Resolver;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class GetProductByEntityId implements ResolverInterface
{
    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $productCollectionFactory;

    /**
     * @var StockItemRepository
     */
    protected StockItemRepository $stockItemRepository;

    /**
     * GetProductByEntityId constructor.
     * @param CollectionFactory $productCollectionFactory
     * @param StockItemRepository $stockItemRepository
     */
    public function __construct(
        CollectionFactory $productCollectionFactory,
        StockItemRepository $stockItemRepository
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->stockItemRepository = $stockItemRepository;
    }

    /**
     * Resolve function
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|Value|mixed|null
     * @throws GraphQlNoSuchEntityException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $product = $this->productCollectionFactory->create()
            ->addAttributeToSelect("*")
            ->addFieldToFilter("entity_id", ["eq" => $args['entity_id']])
            ->getFirstItem();
        if (!$product->getEntityId()) {
            throw new GraphQlNoSuchEntityException(__("Product with entity id = %1 doesn't exist", $args['entity_id']));
        }
        $qty = $this->stockItemRepository->get($product->getEntityId())->getQty();
        $product->setData('qty', $qty);
        return $product->getData();
    }
}
