<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulApi
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulApi\Controller\Extra;

class SearchTermList extends AbstractMobikul
{
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $termBlock = $this->queryCollection
                ->addFieldToFilter("store_id", [["finset"=>[$this->storeId]]])
                ->setPopularQueryFilter($this->storeId);
            $maxPopularity = $termBlock->getFirstitem()->getPopularity();
            $minPopularity = $termBlock->getFirstitem()->getPopularity();
            $range = $maxPopularity - $minPopularity;
            $range = $range == 0 ? 1 : $range;
            if ($termBlock->getSize() > 0) {
                foreach ($termBlock as $term) {
                    $eachTerm = [];
                    $eachTerm["ratio"] = ((($term->getPopularity() - $minPopularity) / $range));
                    if ($eachTerm["ratio"] < 0) {
                        $eachTerm["ratio"] = 0;
                    } else {
                        $eachTerm["ratio"] *= 70 ;
                        $eachTerm["ratio"] += 75 ;
                    }
                    $eachTerm["term"] = $this->helperCatalog->stripTags($term->getQueryText());
                    $this->returnArray["termList"][] = $eachTerm;
                }
            }
            $this->returnArray["success"] = true;
            $this->emulate->stopEnvironmentEmulation($environment);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->storeId = $this->wholeData["storeId"] ?? 0;
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
