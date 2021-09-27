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

class CmsData extends AbstractMobikul
{
    /**
     * Execute Function for class CmsData
     *
     * @return json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $cmsPage = $this->cmsPage->load($this->id);
            $this->returnArray["title"] = $cmsPage->getTitle();
            $this->returnArray["content"] = $this->filterProvider->getBlockFilter()->filter($cmsPage->getContent());
            $this->returnArray["success"] = true;
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    /**
     * Function to verify the request
     *
     * @return void|json
     */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->id = $this->wholeData["id"] ?? 0;
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
