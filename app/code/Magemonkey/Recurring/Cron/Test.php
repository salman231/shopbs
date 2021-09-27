<?php

namespace Magemonkey\Recurring\Cron;
use \Psr\Log\LoggerInterface;
class Test
{
	protected $logger;

	public function __construct(LoggerInterface $logger) {
		$this->logger = $logger;
	}
	public function execute()
	{

		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/cron.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		$logger->info(__METHOD__);
		$logger->info("Cron Works");

		return $this;

	}
}
