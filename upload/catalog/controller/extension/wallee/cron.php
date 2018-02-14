<?php
require_once modification(DIR_SYSTEM . 'library/wallee/helper.php');

class ControllerExtensionWalleeCron extends Controller {

	public function index(){
		$this->endRequestPrematurely();
		
		if (isset($this->request->get['security_token'])) {
			$security_token = $this->request->get['security_token'];
		}
		else {
			\WalleeHelper::instance($this->registry)->log('Cron called without security token.', \WalleeHelper::LOG_ERROR);
			die();
		}
		
		try {
			\WalleeHelper::instance($this->registry)->dbTransactionStart();
			$result = \Wallee\Entity\Cron::setProcessing($this->registry, $security_token);
			\WalleeHelper::instance($this->registry)->dbTransactionCommit();
			if (!$result) {
				die();
			}
		}
		catch (Exception $e) {
			// 1062 is mysql duplicate constraint error. This is expected and doesn't need to be logged.
			if (strpos('1062', $e->getMessage()) === false && strpos('constraint_key', $e->getMessage()) === false) {
				\WalleeHelper::instance($this->registry)->log('Updating cron failed: ' . $e->getMessage(), \WalleeHelper::LOG_ERROR);
			}
			\WalleeHelper::instance($this->registry)->dbTransactionRollback();
			die();
		}
		
		$errors = $this->runTasks();
		
		try {
			\WalleeHelper::instance($this->registry)->dbTransactionStart();
			$result = \Wallee\Entity\Cron::setComplete($this->registry, $security_token, implode('. ', $errors));
			\WalleeHelper::instance($this->registry)->dbTransactionCommit();
			if (!$result) {
				\WalleeHelper::instance($this->registry)->log('Could not update finished cron job.', \WalleeHelper::LOG_ERROR);
				die();
			}
		}
		catch (Exception $e) {
			\WalleeHelper::instance($this->registry)->dbTransactionRollback();
			\WalleeHelper::instance($this->registry)->log('Could not update finished cron job: ' . $e->getMessage(), \WalleeHelper::LOG_ERROR);
			die();
		}
		die();
	}

	private function runTasks(){
		$errors = array();
		foreach (\Wallee\Entity\AbstractJob::loadNotSent($this->registry) as $job) {
			try {
				switch (get_class($job)) {
					case \Wallee\Entity\CompletionJob::class:
						$transaction_info = \Wallee\Entity\TransactionInfo::loadByTransaction($this->registry, $job->getSpaceId(),
								$job->getTransactionId());
						\Wallee\Service\Transaction::instance($this->registry)->updateLineItemsFromOrder($transaction_info->getOrderId());
						\Wallee\Service\Completion::instance($this->registry)->send($job);
						break;
					case \Wallee\Entity\RefundJob::class:
						\Wallee\Service\Refund::instance($this->registry)->send($job);
						break;
					case \Wallee\Entity\VoidJob::class:
						\Wallee\Service\VoidJob::instance($this->registry)->send($job);
						break;
					default:
						break;
				}
			}
			catch (Exception $e) {
				\WalleeHelper::instance($this->registry)->log('Could not update job: ' . $e->getMessage(), \WalleeHelper::LOG_ERROR);
				$errors[] = $e->getMessage();
			}
		}
	}

	private function endRequestPrematurely(){
		ob_end_clean();
		// Return request but keep executing
		set_time_limit(0);
		ignore_user_abort(true);
		ob_start();
		if (session_id()) {
			session_write_close();
		}
		header("Content-Encoding: none");
		header("Connection: close");
		header('Content-Type: text/javascript');
		ob_end_flush();
		flush();
		if (is_callable('fastcgi_finish_request')) {
			fastcgi_finish_request();
		}
	}
}