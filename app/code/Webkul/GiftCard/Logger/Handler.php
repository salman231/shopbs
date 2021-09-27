<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_GiftCard
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\GiftCard\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level
     * @var int
     */
    public $loggerType = Logger::INFO;
 
    /**
     * File name
     * @var string
     */
    public $fileName = '/var/log/GiftCard.log';
}
