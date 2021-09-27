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

use Webkul\MobikulCore\Api\Data\CacheInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface CacheRepositoryInterface
 */
interface CacheRepositoryInterface
{
    /**
     * Function getById
     *
     * @param integer $cacheId cacheId
     */
    public function getById($cacheId);

    /**
     * Function deleteById
     *
     * @param integer $cacheId cacheId
     */
    public function deleteById($cacheId);

    /**
     * Function save
     *
     * @param CacheInterface $cache cache
     */
    public function save(CacheInterface $cache);

    /**
     * Function delete
     *
     * @param CacheInterface $cache cache
     */
    public function delete(CacheInterface $cache);

    /**
     * Function getList
     *
     * @param SearchCriteriaInterface $searchCriteria searchCriteria
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
