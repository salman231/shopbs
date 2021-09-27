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

namespace Webkul\MobikulCore\Block\Adminhtml\Edit\Bannerimage;

use Webkul\MobikulCore\Block\Adminhtml\Edit\GenericButton;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class DeleteButton block
 */
class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Function to getButtonData
     *
     * @return array $data
     */
    public function getButtonData()
    {
        $bannerimageId = $this->getBannerimageId();
        $data = [];
        if ($bannerimageId) {
            $data = [
                "id" => "banner-edit-delete-button",
                "label" => __("Delete Bannner"),
                "class" => "delete",
                "on_click" => "",
                "sort_order" => 20,
                "data_attribute" => ["url"=>$this->getDeleteUrl()],
            ];
        }
        return $data;
    }

    /**
     * Function to get Delete Url
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl("*/*/delete", ["id" => $this->getBannerimageId()]);
    }
}
