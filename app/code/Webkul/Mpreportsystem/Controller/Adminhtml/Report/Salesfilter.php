<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mpreportsystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpreportsystem\Controller\Adminhtml\Report;

use Magento\Backend\App\Action;
use Magento\Framework\Encryption\Helper\Security;
use Magento\Framework\Controller\Result;
use Webkul\Mpreportsystem\Block\Adminhtml\Mpreport;

class Salesfilter extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @var Webkul\Mpreportsystem\Block\Adminhtml\Mpreport
     */
    protected $_mpreportBlock;

    /**
     * @param Action\Context                      $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param Mpreport                            $mpreport
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        Mpreport $mpreport
    ) {
        $this->_mpreportBlock = $mpreport;
        $this->_jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            'Webkul_Mpreportsystem::mpreports'
        );
    }

    /**
     * Salesfilter action
     * returns filtered sales as per the seller
     *
     * @return void
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $returnData = '';
        if (array_key_exists('data', $params)) {
            $returnData = $this->_mpreportBlock
                ->getsalesAmount($params['data']);
        }
        return $this->getResponse()->representJson(
            $this->_jsonHelper->jsonEncode($returnData)
        );
    }
}
