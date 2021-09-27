<?php
/**
 * Webkul Software.
 *
 * PHP version 7.0+
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Controller\Api;

use Magento\Framework\Exception\LocalizedException;
use Webkul\DeliveryBoy\Api\Data\DeliveryboyInterface as DeliveryboyInterface;

class GetDeliveryboyReviewList extends AbstractRating
{
    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $this->validateRequest();
            $this->extractRequestData();
            $this->authorize();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);

            $deliveryboyCollection = $this->deliveryboyResourceCollection->create();
            $this->applyBeforeFiltersDeliveryboyResourceCollection($deliveryboyCollection);
            $deliveryboyCollection->getSelect()->reset(\Zend_Db_Select::COLUMNS);

            $deliveryboyCollection->join(
                ['deliveryboy_rating' => $this->resource->getTableName('deliveryboy_rating')],
                'main_table.id = deliveryboy_rating.deliveryboy_id',
                ['deliveryboy_rating.*']
            );

            $this->applyAfterFiltersDeliveryboyResourceCollection($deliveryboyCollection);
            $totalCount = $this->applyPaginationDeliveryboyResourceCollection($deliveryboyCollection);

            $deliveryboyReviewList = [];
            
            foreach ($deliveryboyCollection as $deliveryboy) {
                $deliveryboyReviewList[] = $deliveryboy->getData();
            }
            $logger = \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class);
            $logger->info(json_encode($deliveryboyReviewList, JSON_PRETTY_PRINT));
            $this->returnArray["reviewList"] = $deliveryboyReviewList;
            $this->returnArray["totalCount"] = $totalCount;
            $this->returnArray["success"] = true;
            $this->emulate->stopEnvironmentEmulation($environment);
        } catch (\Throwable $e) {
            $this->returnArray["message"] = __($e->getMessage());
        }

        return $this->getJsonResponse($this->returnArray);
    }

    /**
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection $collection
     * @return \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection
     */
    protected function applyBeforeFiltersDeliveryboyResourceCollection(
        \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection $collection
    ): \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection {
        if ($this->deliveryboyId || $this->isDeliveryboy()) {
            $collection->addFieldToFilter(
                DeliveryboyInterface::ID,
                $this->isDeliveryboy() ? $this->userId : $this->deliveryboyId
            );
        }

        return $collection;
    }
    
    /**
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection $collection
     * @return \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection
     */
    protected function applyAfterFiltersDeliveryboyResourceCollection(
        \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection $collection
    ): \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection {
        
        return $collection;
    }

    /**
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection $collection
     * @return int
     */
    protected function applyPaginationDeliveryboyResourceCollection(
        \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection $collection
    ): int {
        $totalCount = $collection->getSize();
        if ($this->pageNumber >= 1) {
            $pageSize = $this->deliveryboyHelper->getPageSize();
            $collection->setPageSize($pageSize)->setCurPage($this->pageNumber);
        }

        return $totalCount;
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function validateRequest()
    {
        if (!($this->getRequest()->getMethod() == "GET" && $this->wholeData)) {
            throw new LocalizedException(__("Invalid request."));
        }
    }

    /**
     * @return void
     */
    public function extractRequestData()
    {
        $this->storeId = trim($this->wholeData["storeId"] ??
                $this->storeManager->getDefaultStoreView()->getId());
        $this->isDeliveryboy = trim($this->wholeData["isDeliveryboy"] ?? false);
        $this->userId = trim($this->wholeData["userId"] ?? 0);
        $this->pageNumber = trim($this->wholeData["pageNumber"] ?? 1);
        $this->deliveryboyId = trim($this->wholeData["deliveryboyId"] ?? 0);
        $this->adminCustomerEmail = trim($this->wholeData["adminCustomerEmail"] ?? "");
    }

    /**
     * @return bool
     * @throws LocalizedException
     */
    protected function authorize(): bool
    {
        if (!(
            $this->isAdmin() || (
            $this->isDeliveryboy() && $this->isDeliveryboyExists($this->deliveryboyId)
            )
        )) {
            throw new LocalizedException(__('Unauthorized access.'));
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function isDeliveryboy(): bool
    {
        return ($this->isDeliveryboy) && ($this->userId > 0);
    }

    /**
     * @param int $deliveryboyId
     * @return bool
     */
    protected function isDeliveryboyExists(int $deliveryboyId): bool
    {
        return $this->deliveryboyResourceCollection->create()
            ->addFieldToFilter(DeliveryboyInterface::ID, $deliveryboyId)
            ->getFirstItem()->getId() == $deliveryboyId;
    }

    /**
     * @return bool
     */
    protected function isAdmin(): bool
    {
        return $this->adminCustomerEmail === $this->deliveryboyHelper->getAdminEmail();
    }
}
