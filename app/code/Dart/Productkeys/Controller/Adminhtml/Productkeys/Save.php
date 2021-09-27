<?php
/**
 * Dart Productkeys Record Save Controller.
 * @package    Dart_Productkeys
 *
 */
namespace Dart\Productkeys\Controller\Adminhtml\Productkeys;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Catalog\Model\ProductFactory;
use Dart\Productkeys\Model\ProductkeysFactory;
use Dart\Productkeys\Helper\Data;
use Dart\Productkeys\Controller\Adminhtml\Productkeys\Generatekeys;

class Save extends Action
{
    /**
     * @var \Dart\Productkeys\Model\ProductkeysFactory
     */
    private $productkeysFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Dart\Productkeys\Model\ProductkeysFactory $productkeysFactory
     */
    public function __construct(
        Context $context,
        DateTime $date,
        ProductFactory $productFactory,
        Data $helperData,
        ProductkeysFactory $productkeysFactory,
        Generatekeys $generateKeys
    ) {
        parent::__construct($context);
        $this->_date = $date;
        $this->productFactory = $productFactory;
        $this->helperData = $helperData;
        $this->productkeysFactory = $productkeysFactory;
        $this->generateKeys = $generateKeys;
    }

    public function execute()
    {
        $data = $this->getRequest()->getParams();
		$data['updated_at'] = $this->_date->gmtDate();
        $productId = $this->getRequest()->getParam('product_id');
        if (!$data) {
            $this->_redirect('productkeys/productkeys/addrow');
            return;
        }
        try {
            $pkeyIds = $this->getRequest()->getParam('id');
            $pkeys = $this->getRequest()->getParam('product_key');
            $result = $this->saveProductKeys($data, $productId, $pkeys, $pkeyIds);

            if ($productId) {
                $message = __('Product Key issued successfully!');
            } elseif ($pkeyIds) {
                $message = __('Product Key "%1" Successfully Edited.', $result['key']);
            } else {
                $message = __('%1 Product Key(s) Successfully Added.', $result['count']);
            }
            $this->messageManager->addSuccess($message);
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }

        if (empty($productId)) {
            $this->_redirect('productkeys/productkeys/index');
        } else {
            $this->_redirect('sales/order/view', ['order_id' => $data['order_id']]);
        }
    }

    private function saveProductKeys($data, $productId, $pkeys, $pkeyIds)
    {
		
        $val = [];
        $val['count'] = 0;
        $keyIds = explode(',', $pkeyIds);
        if (!empty($productId)) {
            $keys = trim(preg_replace('/\n+/', ',', $pkeys));
            if (empty($pkeyIds)) {
                $_product = $this->productFactory->create()->load($productId);
                $sku = $_product->getProductkeyPool();
                if (empty($sku)) {
                    $sku = $_product->getSku();
                }
                $data['sku'] = $sku;
            }
        }
        $key_ids = [];
        foreach (explode("\n", $pkeys) as $index => $key) {
            $val['key'] = $key;
            $rowData = $this->productkeysFactory->create();
            if (!empty($keyIds[$index])) {
                $data['id'] = $keyIds[$index];
            } else {
                unset($data['id']);
                $data['created_at'] = $data['updated_at'];
            }
            if (!empty($productId)) {
                $data['type'] = 'From Order';
            }
            $data['product_key'] = $key;
            $savedData = $rowData->setData($data)->save();
            $key_ids[] = $savedData->getId();
            $sku = $savedData->getSku();
            $val['count']++;
        }

        if (empty($productId)) {
            $productUpdated = $this->helperData->changeQty($sku);
            $txt = 'A total of %1 product(s) quantity that are linked/mapped with Keypool "%2" has been updated.';
            if ($productUpdated > 0) {
                $prdMessage = __($txt, $productUpdated, $sku);
                $this->messageManager->addSuccess($prdMessage);
            }
        } else {
            $this->generateKeys->saveKeysToOrderItems(
                $data['order_id'],
                $data['item_id'],
                $keys,
                implode(',', $key_ids),
                $sku,
                $data['prdkey_type'],
                count($key_ids)
            );
        }
        return $val;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Dart_Productkeys::add_row');
    }
}
