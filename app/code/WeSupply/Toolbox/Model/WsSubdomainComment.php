<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WeSupply\Toolbox\Model;

use Magento\Framework\Phrase;
use Magento\Config\Model\Config\CommentInterface;
use WeSupply\Toolbox\Helper\Data as Helper;

/**
 * Class WsSubdomainComment
 * @package WeSupply\Toolbox\Model
 */
class WsSubdomainComment implements CommentInterface
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * WsSubdomainComment constructor.
     * @param Helper $helper
     */
    public function __construct(
        Helper $helper
    )
    {
        $this->helper = $helper;
    }

    /**
     * @param string $elementValue
     * @return Phrase|string
     */
    public function getCommentText($elementValue)
    {
        $commentEl = '';
        $weSupplyDomain = $this->helper->getWeSupplyDomain();
        $weSupplySubdomain = $this->helper->getClientNameByScope();

        if ($weSupplySubdomain != 'install') {
            $commentEl .= '<span id="wesupply_api_integration_wesupply_subdomain">' . $weSupplySubdomain . '.' . $weSupplyDomain . '</span>';
        } else {
            $commentEl .= '<span id="wesupply_api_integration_wesupply_subdomain">' . __('Will be displayed after a WeSupply account is connected with this Magento store.') . '</span>';
        }

        $commentEl .= '<span class="comment">';
        $commentEl .= __('Your WeSupply URL has two parts: a subdomain name you chose when you set up your account, followed by ') . '<strong>' . $weSupplyDomain . '</strong>';
        $commentEl .= ' (' . __('for example: ') . '<strong>' . 'mycompany.' . $weSupplyDomain . '</strong>).';
        $commentEl .= '</span>';

        return $commentEl;
    }
}