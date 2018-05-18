<?php
require_once modification(DIR_SYSTEM . 'library/wallee/helper.php');
use Wallee\Model\AbstractModel;

/**
 * Handles the button on the order info page.
 */
class ModelExtensionWalleeOrder extends AbstractModel {

	/**
	 * Returns all jobs with status FAILED_CHECK, and moves these into state FAILED_DONE.
	 *
	 * @param int $order_id
	 * @return array
	 */
	public function getFailedJobs($order_id){
		$this->language->load('payment/wallee');
		$jobs = array_merge($this->getJobMessages(\Wallee\Entity\VoidJob::loadFailedCheckedForOrder($this->registry, $order_id)),
				$this->getJobMessages(\Wallee\Entity\CompletionJob::loadFailedCheckedForOrder($this->registry, $order_id)),
				$this->getJobMessages(\Wallee\Entity\RefundJob::loadFailedCheckedForOrder($this->registry, $order_id)));
		\Wallee\Entity\VoidJob::markFailedAsDone($this->registry, $order_id);
		\Wallee\Entity\CompletionJob::markFailedAsDone($this->registry, $order_id);
		\Wallee\Entity\RefundJob::markFailedAsDone($this->registry, $order_id);
		return $jobs;
	}

	public function getButtons($order_id){
		$this->language->load('payment/wallee');
		if (!isset($this->request->get['order_id'])) {
			return array();
		}
		$transaction_info = \Wallee\Entity\TransactionInfo::loadByOrderId($this->registry, $order_id);
		if ($transaction_info->getId() == null) {
			return array();
		}
		
		$buttons = array();
		
		if (\WalleeHelper::instance($this->registry)->isCompletionPossible($transaction_info)) {
			$buttons[] = $this->getCompletionButton();
			$buttons[] = $this->getVoidButton();
		}
		
		if (\WalleeHelper::instance($this->registry)->isRefundPossible($transaction_info)) {
			$buttons[] = $this->getRefundButton();
		}
		
		if (\WalleeHelper::instance($this->registry)->hasRunningJobs($transaction_info)) {
			$buttons[] = $this->getUpdateButton();
		}
		
		return $buttons;
	}

	/**
	 *
	 * @param \Wallee\Entity\AbstractJob[] $jobs
	 */
	private function getJobMessages($jobs){
		$job_messages = array();
		foreach ($jobs as $job) {
			$format = $this->language->get('wallee_failed_job_message');
			
			if ($job instanceof \Wallee\Entity\CompletionJob) {
				$type = $this->language->get('completion_job');
			}
			else if ($job instanceof \Wallee\Entity\RefundJob) {
				$type = $this->language->get('refund_job');
			}
			else if ($job instanceof \Wallee\Entity\VoidJob) {
				$type = $this->language->get('void_job');
			}
			else {
				$type = get_class($job);
			}
			
			$format = '%s %s: %s';
			$job_messages[] = sprintf($format, $type, $job->getJobId(), $job->getFailureReason());
		}
		return $job_messages;
	}

	private function getVoidButton(){
		return array(
			'text' => $this->language->get('button_void'),
			'icon' => 'ban',
			'route' => 'extension/wallee/void' 
		);
	}

	private function getCompletionButton(){
		return array(
			'text' => $this->language->get('button_complete'),
			'icon' => 'check',
			'route' => 'extension/wallee/completion' 
		);
	}

	private function getRefundButton(){
		return array(
			'text' => $this->language->get('button_refund'),
			'icon' => 'reply',
			'route' => 'extension/wallee/refund/page' 
		);
	}

	private function getUpdateButton(){
		return array(
			'text' => $this->language->get('button_update'),
			'icon' => 'refresh',
			'route' => 'extension/wallee/update' 
		);
	}
}