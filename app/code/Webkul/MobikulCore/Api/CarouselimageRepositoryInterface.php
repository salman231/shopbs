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
use Webkul\MobikulCore\Api\Data\CarouselimageInterface;

/**
 * Interface CarouselimageRepositoryInterface
 */
interface CarouselimageRepositoryInterface
{
    /**
     * Function getById
     *
     * @param integer $carouselImageId carouselImageId
     */
    public function getById($carouselImageId);

    /**
     * Function deleteById
     *
     * @param integer $carouselImageId carouselImageId
     */
    public function deleteById($carouselImageId);

    /**
     * Function save
     *
     * @param CarouselimageInterface $carouselImage carouselImage
     */
    public function save(CarouselimageInterface $carouselImage);

    /**
     * Function delete
     *
     * @param CarouselimageInterface $carouselImage carouselImage
     */
    public function delete(CarouselimageInterface $carouselImage);

    /**
     * Function getList
     *
     * @param SearchCriteriaInterface $searchCriteria searchCriteria
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
