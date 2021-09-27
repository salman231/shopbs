<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Model;

use Amasty\Customform\Api\Data;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Amasty\Customform\Model\ResourceModel\Form as FormResource;
use Amasty\Customform\Model\FormFactory;
use Amasty\Customform\Model\ResourceModel\Form\CollectionFactory as FormCollectionFactory;

class FormRepository implements \Amasty\Customform\Api\FormRepositoryInterface
{
    /**
     * @var array
     */
    protected $form = [];

    /**
     * @var ResourceModel\Form
     */
    private $formResource;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var ResourceModel\Form\CollectionFactory
     */
    private $formCollectionFactory;

    public function __construct(
        FormResource $formResource,
        FormFactory $formFactory,
        FormCollectionFactory $formCollectionFactory
    ) {
        $this->formResource = $formResource;
        $this->formFactory = $formFactory;
        $this->formCollectionFactory = $formCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Data\FormInterface $form)
    {
        if ($form->getFormId()) {
            $form = $this->get($form->getFormId())->addData($form->getData());
        }

        try {
            $this->formResource->save($form);
            unset($this->form[$form->getFormId()]);
        } catch (\Exception $e) {
            if ($form->getFormId()) {
                throw new CouldNotSaveException(
                    __('Unable to save request with ID %1. Error: %2', [$form->getFormId(), $e->getMessage()])
                );
            }
            throw new CouldNotSaveException(__('Unable to save new request. Error: %1', $e->getMessage()));
        }
        
        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function get($formId)
    {
        if (!isset($this->form[$formId])) {
            /** @var \Amasty\Customform\Model\Form $form */
            $form = $this->formFactory->create();
            $this->formResource->load($form, $formId);
            if (!$form->getFormId()) {
                throw new NoSuchEntityException(__('Form with specified ID "%1" not found.', $formId));
            }
            $this->form[$formId] = $form;
        }
        return $this->form[$formId];
    }

    /**
     * {@inheritdoc}
     */
    public function getByFormCode($formCode)
    {
        if ($formCode) {
            $form = $this->formFactory->create();
            $this->formResource->load($form, $formCode, \Amasty\Customform\Api\Data\FormInterface::CODE);

            if ($form->getFormId()) {
                return $form;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Data\FormInterface $form)
    {
        try {
            $this->formResource->delete($form);
            unset($this->form[$form->getFormId()]);
        } catch (\Exception $e) {
            if ($form->getFormId()) {
                throw new CouldNotDeleteException(
                    __('Unable to remove form with ID %1. Error: %2', [$form->getFormId(), $e->getMessage()])
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove form. Error: %1', $e->getMessage()));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($formId)
    {
        $model = $this->get($formId);
        $this->delete($model);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getList()
    {
        $formCollection = $this->formCollectionFactory->create();
        $formList = [];

        foreach ($formCollection as $form) {
            $formList[] = $form;
        }

        return $formList;
    }
}
