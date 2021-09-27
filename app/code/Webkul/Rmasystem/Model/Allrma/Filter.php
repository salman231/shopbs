<?php

namespace Webkul\Rmasystem\Model\Allrma;

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
}
