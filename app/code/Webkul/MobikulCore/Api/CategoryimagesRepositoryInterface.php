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
use Webkul\MobikulCore\Api\Data\CategoryimagesInterface;

/**
 * Interface CategoryimagesRepositoryInterface
 */
interface CategoryimagesRepositoryInterface
{
    /**
     * Function getById
     *
     * @param integer $categoryimagesId categoryimagesId
     */
    public function getById($categoryimagesId);

    /**
     * Function deleteById
     *
     * @param integer $categoryimagesId categoryimagesId
     *
     * @return null
     */
    public function deleteById($categoryimagesId);

    /**
     * Function save
     *
     * @param CategoryimagesInterface $categoryimages categoryimages
     */
    public function save(CategoryimagesInterface $categoryimages);

    /**
     * Function delete
     *
     * @param CategoryimagesInterface $categoryimages categoryimages
     *
     * @return null
     */
    public function delete(CategoryimagesInterface $categoryimages);

    /**
     * Function getList
     *
     * @param SearchCriteriaInterface $searchCriteria searchCriteria
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
