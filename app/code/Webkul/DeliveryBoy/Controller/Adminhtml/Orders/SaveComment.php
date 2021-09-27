<?php
/**
 * Webkul Software.
 *
 * PHP version 7.0+
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Controller\Api\Admin;

use Magento\Framework\LocalizedException\LocalizedException;

class SaveComment extends \Magento\Backend\App\Action
{
    /**
     * Current user name
     *
     * @var string
     */
    protected $name;

    /**
     * @var \Webkul\DeliveryBoy\Model\CommentFactory
     */
    protected $deliveryboyCommentFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Webkul\DeliveryBoy\Model\CommentFactory $deliveryboyCommentFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\RequestInterface $request,
        \Webkul\DeliveryBoy\Model\CommentFactory $deliveryboyCommentFactory,
        array $data = []
    ) {
        $this->date = $date;
        $this->request = $request;
        $this->deliveryboyCommentFactory = $deliveryboyCommentFactory;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    public function execute()
    {
        try {
            $wholeData = $this->request->getPostParams();
            $comment = $wholeData["comment"] ?? "";
            $senderId = $wholeData["senderId"] ?? 0;
            $incrementId = $wholeData["incrementId"] ?? "";
            $isDeliveryboy = $wholeData["isDeliveryboy"] ?? false;
            $deliveryboyOrderId = $wholeData["deliveryboyOrderId"] ?? 0;

            if ($comment == "") {
                throw new LocalizedException(__("Comment field is required."));
            }
            if (str_word_count($this->comment < 5)) {
                throw new LocalizedException(__("Comment should be atleast 5 words."));
            }

            if ($senderId == 0) {
                $name = "Admin";
            }

            $this->deliveryboyCommentFactoryFactory->create()
                ->setComment($comment)
                ->setSenderId($senderId)
                ->setIsDeliveryboy($isDeliveryboy)
                ->setOrderIncrementId($incrementId)
                ->setDeliveryboyOrderId($deliveryboyOrderId)
                ->setCommentedBy($name)
                ->setCreatedAt($this->date->gmtDate())
                ->save();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
