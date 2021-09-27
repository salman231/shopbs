<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Model\Template;

use \Magento\Framework\Mail\Template\TransportBuilder as Transport;
use Zend_Mime;

class TransportBuilder extends Transport
{
    /**
     * @var array
     */
    private $parts = [];

    /**
     * @param $body
     * @param null $filename
     * @param string $mimeType
     * @param string $disposition
     * @param string $encoding
     * @return $this
     */
    public function addAttachment(
        $body,
        $filename    = null,
        $mimeType    = Zend_Mime::TYPE_OCTETSTREAM,
        $disposition = Zend_Mime::DISPOSITION_ATTACHMENT,
        $encoding    = Zend_Mime::ENCODING_BASE64
    ) {
        if (method_exists($this->message, 'createAttachment')) {
            $this->message->createAttachment(
                $body,
                $mimeType,
                $disposition,
                $encoding,
                $filename
            );
        } else {
            $mp = new \Zend\Mime\Part($body);
            $mp->encoding = $encoding;
            $mp->type = $mimeType;
            $mp->disposition = $disposition;
            $mp->filename = $filename;

            $this->parts[] = $mp;
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function prepareMessage()
    {
        parent::prepareMessage();
        if (!empty($this->parts)) {
            /** @var \Zend\Mime\Part $part */
            foreach ($this->parts as $part) {
                $this->message->getBody()->addPart($part);
            }
            $this->message->setBody($this->message->getBody());
        }

        return $this;
    }
}
