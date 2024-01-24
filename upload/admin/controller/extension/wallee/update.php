<?php
/**
 * Wallee OpenCart
 *
 * This OpenCart module enables to process payments with Wallee (https://www.wallee.com).
 *
 * @package Whitelabelshortcut\Wallee
 * @author wallee AG (https://www.wallee.com)
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache Software License (ASL 2.0)
 */
require_once modification(DIR_SYSTEM . 'library/wallee/helper.php');

class ControllerExtensionWalleeUpdate extends \Wallee\Controller\AbstractController {

	public function index(){
		$this->response->addHeader('Content-Type: application/json');
		
		try {
			$this->validate();
			
			$message = $this->language->get('message_refresh_success');
			
			$transaction_info = \Wallee\Entity\TransactionInfo::loadByOrderId($this->registry, $this->request->get['order_id']);
			if ($transaction_info->getId() === null) {
				throw new Exception($this->language->get('error_not_wallee'));
			}
			
			$completion_job = \Wallee\Entity\CompletionJob::loadNotSentForOrder($this->registry, $this->request->get['order_id']);
			if ($completion_job->getId()) {
				\Wallee\Service\Completion::instance($this->registry)->send($completion_job);
				$message .= '<br/>' . sprintf($this->language->get('message_resend_completion'), $completion_job->getId());
			}
			
			$void_job = \Wallee\Entity\VoidJob::loadNotSentForOrder($this->registry, $this->request->get['order_id']);
			if ($void_job->getId()) {
				\Wallee\Service\VoidJob::instance($this->registry)->send($void_job);
				$message .= '<br/>' . sprintf($this->language->get('message_resend_void'), $void_job->getId());
			}
			
			$refund_job = \Wallee\Entity\RefundJob::loadNotSentForOrder($this->registry, $this->request->get['order_id']);
			if ($refund_job->getId()) {
				\Wallee\Service\Refund::instance($this->registry)->send($refund_job);
				$message .= '<br/>' . sprintf($this->language->get('message_resend_refund'), $refund_job->getId());
			}
			
			$this->load->model('extension/wallee/order');
			$new_buttons = $this->model_extension_wallee_order->getButtons($this->request->get['order_id']);
			
			$this->response->setOutput(json_encode([
				'success' => $message,
				'buttons' => $new_buttons 
			]));
			return;
		}
		catch (Exception $e) {
			$this->response->setOutput(json_encode([
				'error' => $e->getMessage() 
			]));
		}
	}

	protected function getRequiredPermission(){
		return 'extension/wallee/update';
	}
}