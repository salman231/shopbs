<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MagentoChatSystem\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface ReportRepositoryInterface
{

    /**
     * Save Report
     * @param \Webkul\MagentoChatSystem\Api\Data\ReportInterface $report
     * @return \Webkul\MagentoChatSystem\Api\Data\ReportInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Webkul\MagentoChatSystem\Api\Data\ReportInterface $report
    );

    /**
     * Retrieve Report
     * @param string $reportId
     * @return \Webkul\MagentoChatSystem\Api\Data\ReportInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($reportId);

    /**
     * Retrieve Report matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Webkul\MagentoChatSystem\Api\Data\ReportSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Report
     * @param \Webkul\MagentoChatSystem\Api\Data\ReportInterface $report
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Webkul\MagentoChatSystem\Api\Data\ReportInterface $report
    );

    /**
     * Delete Report by ID
     * @param string $reportId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($reportId);
}
