<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Simpledetailconfigurable\Helper;

use Magento\Framework\App\Helper\Context;

class UrlIdentifier extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * @var \Magento\UrlRewrite\Model\UrlRewriteFactory
     */
    private $urlRewriteFactory;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    private $configurableData;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productInfo;

    /**
     * UrlIdentifier constructor.
     *
     * @param Context $context
     * @param ModuleConfig $moduleConfig
     * @param \Magento\Catalog\Model\ProductRepository $productInfo
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableData
     * @param \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewriteFactory
     */
    public function __construct(
        Context $context,
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $moduleConfig,
        \Magento\Catalog\Model\ProductRepository $productInfo,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableData,
        \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewriteFactory
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->configurableData = $configurableData;
        $this->productInfo = $productInfo;
        parent::__construct($context);
    }

    /**
     * @param string $url
     * @return array
     */
    public function readUrl($url)
    {
        $result = ['product' => '0'];
        $productInfo = explode('+', $url);
        $urlPart = explode('/', $productInfo[0]);
        array_shift($urlPart);
        $productKey = implode('/', $urlPart);
        $urlRewrite = $this->getProductId($productKey);
        if ($urlRewrite) {
            $result['product'] = $urlRewrite->getEntityId();
            if ($urlRewrite->getMetadata() && isset($urlRewrite->getMetadata()['category_id'])) {
                $result['category'] = $urlRewrite->getMetadata()['category_id'];
            } else {
                $result['category'] = null;
            }
        }
        return $result;
    }

    /**
     * @param string $urlKey
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductId($urlKey)
    {
        return $this->urlRewriteFactory->create()->getCollection()
            ->addFieldToFilter('entity_type', 'product')
            ->addFieldToFilter('request_path', ['like' => $urlKey . $this->moduleConfig->getSuffix()])
            ->getItemByColumnValue('store_id', $this->moduleConfig->getStoreId());
    }

    /**
     * @param string $url
     * @return \Magento\Catalog\Model\Product|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getChildProduct($url)
    {
        $superData = explode('+', $url);
        $product = array_shift($superData);
        $rewriteModel = $this->getProductId($product);
        if (!$rewriteModel) {
            return null;
        }
        $productId = $rewriteModel->getEntityId();
        $product = $this->productInfo->getById($productId);
        $parentAttribute = $this->configurableData->getConfigurableAttributes($product);
        foreach ($parentAttribute as $attrValue) {
            $attrCode = $attrValue->getProductAttribute()->getAttributeCode();
            $map[$attrCode] = $attrValue->getAttributeId();
            foreach ($product->getAttributes()[$attrValue->getProductAttribute()->getAttributeCode()]
                         ->getOptions() as $tvalue) {
                $labelText = $this->getLabelAsText($tvalue);
                if (!$labelText) {
                    continue;
                }
                $map2[$attrValue->getAttributeId()][$labelText] = $tvalue->getValue();
            }
        }
        $superAttribute = [];
        foreach ($superData as $datas) {
            $data = urldecode($datas);
            $code = substr($data, 0, strpos($data, '-'));
            $value = substr($data, strpos($data, '-') + 1);
            $value = str_replace('~', ' ', $value);
            if (array_key_exists($code, $map) && array_key_exists($map[$code], $map2)) {
                if (isset($map[$code])) {
                    $superAttribute[$map[$code]] = $map2[$map[$code]][$value];
                }
            }
        }
        $child = $this->configurableData->getProductByAttributes($superAttribute, $product);
        return $child;
    }

    /**
     * @param \Magento\Eav\Model\Entity\Attribute\Option $tvalue
     */
    protected function getLabelAsText($tvalue)
    {
        if ($tvalue && is_string($tvalue->getLabel())) {
            return $tvalue->getLabel();
        }
        if ($tvalue->getLabel() instanceof \Magento\Framework\Phrase) {
            /** @var $tvalue->getLabel() \Magento\Framework\Phrase */
            return $tvalue->getLabel()->getText();
        }
        return false;
    }
}
