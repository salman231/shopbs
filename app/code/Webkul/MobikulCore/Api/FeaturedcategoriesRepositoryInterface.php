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
use Webkul\MobikulCore\Api\Data\FeaturedcategoriesInterface;

/**
 * Interface FeaturedcategoriesRepositoryInterface
 */
interface FeaturedcategoriesRepositoryInterface
{
    /**
     * Function getById
     *
     * @param integer $featuredcategoriesId featuredcategoriesId
     */
    public function getById($featuredcategoriesId);

    /**
     * Function deleteById
     *
     * @param integer $featuredcategoriesId featuredcategoriesId
     */
    public function deleteById($featuredcategoriesId);

    /**
     * Function getList
     *
     * @param SearchCriteriaInterface $searchCriteria searchCriteria
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Function save
     *
     * @param FeaturedcategoriesInterface $featuredcategories featuredcategories
     */
    public function save(FeaturedcategoriesInterface $featuredcategories);

    /**
     * Function delete
     *
     * @param FeaturedcategoriesInterface $featuredcategories featuredcategories
     */
    public function delete(FeaturedcategoriesInterface $featuredcategories);
}
