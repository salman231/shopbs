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
use Webkul\MobikulCore\Api\Data\BannerimageInterface;

/**
 * Interface BannerimageRepositoryInterface
 */
interface BannerimageRepositoryInterface
{
    /**
     * Function getById
     *
     * @param integer $bannerimageId bannerimageId
     */
    public function getById($bannerimageId);

    /**
     * Function deleteById
     *
     * @param integer $bannerimageId bannerimageId
     */
    public function deleteById($bannerimageId);

    /**
     * Function save
     *
     * @param BannerimageInterface $bannerimage bannerimage
     */
    public function save(BannerimageInterface $bannerimage);

    /**
     * Function delete
     *
     * @param BannerimageInterface $bannerimage bannerimage
     */
    public function delete(BannerimageInterface $bannerimage);

    /**
     * Function getList
     *
     * @param SearchCriteriaInterface $searchCriteria searchCriteria
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
