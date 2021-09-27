<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Webkul\MobikulCore\Api\Data\NotificationInterface;

/**
 * Interface NotificationRepositoryInterface
 */
interface NotificationRepositoryInterface
{
    /**
     * Function getById
     *
     * @param integer $notificationId notificationId
     */
    public function getById($notificationId);

    /**
     * Function deleteById
     *
     * @param integer $notificationId notificationId
     */
    public function deleteById($notificationId);

    /**
     * Function save
     *
     * @param NotificationInterface $notification notification
     */
    public function save(NotificationInterface $notification);

    /**
     * Function delete
     *
     * @param NotificationInterface $notification notification
     */
    public function delete(NotificationInterface $notification);

    /**
     * Function getList
     *
     * @param SearchCriteriaInterface $searchCriteria searchCriteria
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
