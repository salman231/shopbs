<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Api;

/**
 * Interface FormRepositoryInterface
 * @api
 */
interface FormRepositoryInterface
{
    /**
     * @param \Amasty\Customform\Api\Data\FormInterface $form
     * @return \Amasty\Customform\Api\Data\FormInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Amasty\Customform\Api\Data\FormInterface $form);

    /**
     * @param int $formId
     * @return \Amasty\Customform\Api\Data\FormInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($formId);

    /**
     * @param string $formCode
     * @return Data\FormInterface|bool
     */
    public function getByFormCode($formCode);

    /**
     * @param \Amasty\Customform\Api\Data\FormInterface $form
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\Customform\Api\Data\FormInterface $form);

    /**
     * @param int $formId
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($formId);

    /**
     * Lists
     *
     * @return \Amasty\Customform\Api\Data\FormInterface[] Array of items.
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList();
}
