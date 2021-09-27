<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Helper;

/**
 * Searchsuggestion helper class
 */
class Searchsuggestion extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Webkul\MobikulCore\Helper\Data $helper
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     */
    protected $localeDate;

    /**
     * Constructor function for Helper Class
     *
     * @param \Webkul\MobikulCore\Helper\Data                      $helper     helper
     * @param \Magento\Framework\App\Helper\Context                $context    context
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate $localeDate
     */
    public function __construct(
        \Webkul\MobikulCore\Helper\Data $helper,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
    ) {
        $this->helper = $helper;
        $this->localeDate = $localeDate;
        parent::__construct($context);
    }

    /**
     * Function to check if the product is on sale
     *
     * @param object $product product
     *
     * @return bool
     */
    public function isOnSale($product)
    {
        $specialPrice = number_format($product->getFinalPrice(), 2);
        $regularPrice = number_format($product->getPrice(), 2);
        if ($specialPrice != $regularPrice) {
            return $this->_nowIsBetween($product->getData("special_from_date"), $product->getData("special_to_date"));
        } else {
            return false;
        }
    }

    /**
     * Function to check if the current time is between fro Date and to date
     *
     * @param date $fromDate fromDate
     * @param date $toDate   toDate
     *
     * @return bool
     */
    protected function _nowIsBetween($fromDate, $toDate)
    {
        if ($fromDate) {
            $fromDate = strtotime($fromDate);
            $toDate   = strtotime($toDate);
            $now      = strtotime($this->localeDate->date()->setTime(0, 0, 0)->format("Y-m-d H:i:s"));
            if ($toDate) {
                if ($fromDate <= $now && $now <= $toDate) {
                    return true;
                }
            } else {
                if ($fromDate <= $now) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Function match String
     *
     * @param string $term    term
     * @param string $tagName tagname
     *
     * @return string string
     */
    public function matchString($term, $tagName)
    {
        $str      = "";
        $len      = strlen($term);
        $term1    = strtolower($term);
        $tagName1 = strtolower($tagName);
        if ($term1 != "") {
            $pos      = strpos($tagName1, $term1);
            for ($i=0; $i<$len; $i++) {
                $j = $pos+$i;
                $subTerm  = substr($term, $i, 1);
                $subTerm1 = strtolower($subTerm);
                $subTerm2 = strtoupper($subTerm);
                $subName  = substr($tagName, $j, 1);
                if ($subTerm1 == $subName) {
                    $str .= $subTerm1;
                } elseif ($subTerm2 == $subName) {
                    $str .= $subTerm2;
                }
            }
            return($str);
        } else {
            return("");
        }
    }

    /**
     * Function get Bold Name
     *
     * @param string $tagName tag Name
     * @param string $str     str
     * @param string $term    term
     *
     * @return string $tagName
     */
    public function getBoldName($tagName, $str, $term)
    {
        $len = strlen($term);
        if (strlen($str) >= $len) {
            $tagName = str_replace($str, "<b>".$str."</b>", $tagName);
        }
        return($tagName);
    }

    /**
     * Function displayTags to get Display Tags
     *
     * @return bool
     */
    public function displayTags()
    {
        return (bool)$this->helper->getConfigData("mobikul/searchsuggestion/displaytag");
    }

    /**
     * Function displayProducts to get Display Products
     *
     * @return bool
     */
    public function displayProducts()
    {
        return (bool)$this->helper->getConfigData("mobikul/searchsuggestion/displayproduct");
    }

    /**
     * Function getNumberOfTags to get Number Of Tags
     *
     * @return integer
     */
    public function getNumberOfTags()
    {
        return (int) $this->helper->getConfigData("mobikul/searchsuggestion/tagcount");
    }

    /**
     * Function getNumberOfProducts to get Number Of Products
     *
     * @return integer
     */
    public function getNumberOfProducts()
    {
        return (int) $this->helper->getConfigData("mobikul/searchsuggestion/productcount");
    }
}
