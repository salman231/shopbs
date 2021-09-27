<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface AnswerRepositoryInterface
 * @api
 */
interface AnswerRepositoryInterface
{
    /**
     * @param \Amasty\Customform\Api\Data\AnswerInterface $answer
     * @return \Amasty\Customform\Api\Data\AnswerInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Amasty\Customform\Api\Data\AnswerInterface $answer);

    /**
     * @param int $answerId
     * @return \Amasty\Customform\Api\Data\AnswerInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($answerId);

    /**
     * @param \Amasty\Customform\Api\Data\AnswerInterface $answer
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\Customform\Api\Data\AnswerInterface $answer);

    /**
     * @param int $answerId
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($answerId);

    /**
     * Lists by criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Amasty\Customform\Api\Data\AnswerInterface[] Array of items.
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getListFilter(SearchCriteriaInterface $searchCriteria);

    /**
     * Lists
     *
     * @return \Amasty\Customform\Api\Data\AnswerInterface[] Array of items.
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList();
}
