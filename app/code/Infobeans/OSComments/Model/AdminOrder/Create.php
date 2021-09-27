<?php

namespace Infobeans\OSComments\Model\AdminOrder;

class Create extends \Magento\Sales\Model\AdminOrder\Create
{
    /**
     * Save delivery comment while reorder from admin
     *
     * @param   array $data
     * @return  $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function importPostData($data)
    {
        parent::importPostData($data);
        /**
         * Infobeans changes
         */
        if (isset($data['delivery_comment'])) {
            $this->setDeliveryComment(htmlspecialchars($data['delivery_comment']));
        }

        return $this;
    }
    
    /**
     * Add shipping comment
     *
     * @param string $code
     * @return $this
     */
    public function setDeliveryComment($code)
    {
        $code = trim((string)$code);
        $this->getQuote()->setDeliveryComment($code);
        return $this;
    }
}
