<?php
declare(strict_types=1);

namespace Demo\GraphQL\Model\Resolver;

use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Webapi\ServiceOutputProcessor;

/**
 * Customers field resolver, used for GraphQL request processing.
 */
class Customer implements ResolverInterface
{
    /**
     * @var ValueFactory
     */
    private ValueFactory $valueFactory;

    /**
     * @var CustomerCollectionFactory
     */
    private CustomerCollectionFactory $customerCollectionFactory;

    /**
     * @var ServiceOutputProcessor
     */
    private ServiceOutputProcessor $serviceOutputProcessor;

    /**
     * @var ExtensibleDataObjectConverter
     */
    private ExtensibleDataObjectConverter $dataObjectConverter;

    /**
     * Customer Construct
     *
     * @param ValueFactory $valueFactory
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param ServiceOutputProcessor $serviceOutputProcessor
     * @param ExtensibleDataObjectConverter $dataObjectConverter
     */
    public function __construct(
        ValueFactory $valueFactory,
        CustomerCollectionFactory $customerCollectionFactory,
        ServiceOutputProcessor $serviceOutputProcessor,
        ExtensibleDataObjectConverter $dataObjectConverter
    ) {
        $this->valueFactory = $valueFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->serviceOutputProcessor = $serviceOutputProcessor;
        $this->dataObjectConverter = $dataObjectConverter;
    }

    /**
     * Resolve function
     *
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return Value|mixed
     * @throws GraphQlAuthorizationException
     * @throws GraphQlNoSuchEntityException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($args['email'])) {
            throw new GraphQlAuthorizationException(
                __(
                    'email for customer should be specified',
                    [\Magento\Customer\Model\Customer::ENTITY]
                )
            );
        }
        try {
            $data = $this->getCustomerData($args['email']);
            $result = function () use ($data) {
                return !empty($data) ? $data : [];
            };
            return $this->valueFactory->create($result);
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        }
    }

    /**
     * Get Customer data
     *
     * @param string $customerEmail
     * @return array
     * @throws NoSuchEntityException
     */
    private function getCustomerData(string $customerEmail): array
    {
        try {
            $customerData = [];
            $customerCollection = $this->customerCollectionFactory->create()
                ->addFieldToFilter('email', ['eq' => $customerEmail]);
            foreach ($customerCollection as $customer) {
                array_push($customerData, $customer->getData());
            }
            return isset($customerData[0]) ? $customerData[0] : [];
        } catch (NoSuchEntityException $e) {
            return [];
        } catch (LocalizedException $e) {
            throw new NoSuchEntityException(__($e->getMessage()));
        }
    }
}
