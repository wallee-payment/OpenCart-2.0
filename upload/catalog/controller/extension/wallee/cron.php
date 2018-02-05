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
			\WalleeHelper::instance($this->registry)->log('Updating cron failed: ' . $e->getMessage(), \WalleeHelper::LOG_ERROR);
			\WalleeHelper::instance($this->registry)->dbTransactionRollback();
			die();
		}
		
		$time = new DateTime();
		// We reduce max running time, so th cron has time to clean up.
		$maxTime = $time->format("U");
		$maxTime += \Wallee\Entity\Cron::MAX_RUN_TIME_MINUTES * 60 - 60;
		$maxTime += 5;
		$error = '';
		
		try {
			$this->runTasks();
		}
		catch (Exception $e) {
			$error = "Module '$module' does not handle all exceptions in task '$callableName'. Exception Message: " . $e->getMessage();
		}
		if ($maxTime + 15 < time()) {
			$error += "Module '$module' returns not callable task '$callableName' does not respect the max runtime.";
		}
		
		try {
			\WalleeHelper::instance($this->registry)->dbTransactionStart();
			$result = \Wallee\Entity\Cron::setComplete($this->registry, $security_token, $error);
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
		foreach (\Wallee\Entity\AbstractJob::loadNotSent($this->registry) as $job) {
			try {
				if ($job instanceof \Wallee\Entity\CompletionJob) {
					$transaction_info = \Wallee\Entity\TransactionInfo::loadByTransaction($this->registry, $job->getSpaceId(),
							$job->getTransactionId());
					\Wallee\Service\Transaction::instance($this->registry)->updateLineItemsFromOrder($transaction_info->getOrderId());
				}
				\Wallee\Service\Completion::instance($this->registry)->send($job);
			}
			catch (Exception $e) {
				\WalleeHelper::instance($this->registry)->log('Could not update job: ' . $e->getMessage(), \WalleeHelper::LOG_ERROR);
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
		header('Content-Type: image/png');
		header("Content-Length: 0");
		ob_end_flush();
		flush();
		if (is_callable('fastcgi_finish_request')) {
			fastcgi_finish_request();
		}
	}
}