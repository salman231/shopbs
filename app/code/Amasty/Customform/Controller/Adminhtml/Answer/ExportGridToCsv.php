<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Controller\Adminhtml\Answer;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Amasty\Customform\Model\Export\ConvertToCsv;
use Magento\Framework\App\Response\Http\FileFactory;

class ExportGridToCsv extends Action
{
    const ADMIN_RESOURCE = 'Amasty_Customform::data';

    /**
     * @var ConvertToCsv
     */
    private $converter;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    public function __construct(
        Context $context,
        ConvertToCsv $converter,
        FileFactory $fileFactory
    ) {
        parent::__construct($context);
        $this->converter = $converter;
        $this->fileFactory = $fileFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        return $this->fileFactory->create('export.csv', $this->converter->getCsvFile(), 'var');
    }
}
