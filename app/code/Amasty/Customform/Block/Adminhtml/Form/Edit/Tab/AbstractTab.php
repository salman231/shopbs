<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Block\Adminhtml\Form\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Form\Element\Dependence;

class AbstractTab extends Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return $this->getTabTitle();
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Title');
    }

    /**
     * define field dependencies
     *
     * @param Dependence $dependence
     * @param $parent
     * @param $depend
     */
    protected function addDependencies(Dependence $dependence, $parent, $depend)
    {
        $dependence->addFieldMap($parent->getHtmlId(), $parent->getName())
            ->addFieldMap($depend->getHtmlId(), $depend->getName())
            ->addFieldDependence(
                $depend->getName(),
                $parent->getName(),
                '1'
            );
    }
}
