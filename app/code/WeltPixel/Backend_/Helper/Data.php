<?php

namespace WeltPixel\Backend\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;


    /**
     * Data constructor.
     * @param Context $context
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        Context $context,
        ProductMetadataInterface $productMetadata
    )
    {
        parent::__construct($context);
        $this->productMetadata = $productMetadata;
    }

    /**
     * @return bool
     */
    public function isRecaptchaAvailable()
    {
        $magentoVersion = $this->productMetadata->getVersion();
        if (version_compare($magentoVersion, '2.3.3', '<')) {
            return false;
        }
        return true;
    }


}
