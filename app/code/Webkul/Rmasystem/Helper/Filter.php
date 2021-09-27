<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Rmasystem
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Rmasystem\Helper;

use Magento\Framework\Session\SessionManager;

class Filter
{
    /**
     * @var Session
     */
    protected $session;

    public function __construct(
        SessionManager $session
    ) {
    
        $this->session = $session;
    }

    /**
     * @return array
     */
    public function getSortingSession()
    {
        return $this->session->getSortingSession();
    }
    /**
     * @return array
     */
    public function getFilterSession()
    {
        return $this->session->getFilterData();
    }
    /**
     * @return array
     */
    public function getGuestSortingSession()
    {
        return $this->session->getGuestSortingSession();
    }
    /**
     * @return array
     */
    public function getGuestFilterSession()
    {
        return $this->session->getGuestFilterData();
    }
    /**
     * @return array
     */
    public function getNewRmaSortingSession()
    {
        return $this->session->getNewRmaSortingSession();
    }
    /**
     * @return array
     */
    public function getNewRmaFilterSession()
    {
        return $this->session->getNewRmaFilterData();
    }
    /**
     * @return array
     */
    public function getNewGuestRmaSortingSession()
    {
        return $this->session->getNewGuestSortingSession();
    }
    /**
     * @return array
     */
    public function getNewGuestRmaFilterSession()
    {
        return $this->session->getNewGuestFilterData();
        ;
    }
}
