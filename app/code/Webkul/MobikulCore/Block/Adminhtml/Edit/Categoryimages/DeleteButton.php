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

namespace Webkul\MobikulCore\Block\Adminhtml\Edit\Categoryimages;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Webkul\MobikulCore\Block\Adminhtml\Edit\GenericButton;

/**
 * Class DeleteButton. block
 */
class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $bannnerimageId = $this->getCategoryimagesId();
        $data = [];
        if ($bannnerimageId) {
            $data = [
                "label" => __("Delete Category Image"),
                "class" => "delete",
                "id" => "categoryimages-edit-delete-button",
                "data_attribute" => ["url" => $this->getDeleteUrl()],
                "on_click" => "",
                "sort_order" => 20
            ];
        }
        return $data;
    }

    /**
     * Function to get delete url
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl("*/*/delete", ["id" => $this->getCategoryimagesId()]);
    }
}
