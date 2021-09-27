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

use Webkul\MobikulCore\Api\Data\CarouselInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface CarouselRepositoryInterface
 */
interface CarouselRepositoryInterface
{
    /**
     * Function getById
     *
     * @param integer $carouselId Carousel Id
     */
    public function getById($carouselId);

    /**
     * Function deleteById
     *
     * @param integer $carouselId carousel id
     */
    public function deleteById($carouselId);

    /**
     * Function save
     *
     * @param CarouselInterface $carousel carousel
     */
    public function save(CarouselInterface $carousel);

    /**
     * Function delete
     *
     * @param CarouselInterface $carousel carousel
     */
    public function delete(CarouselInterface $carousel);

    /**
     * Function getList
     *
     * @param SearchCriteriaInterface $searchCriteria SearchCriteria
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
