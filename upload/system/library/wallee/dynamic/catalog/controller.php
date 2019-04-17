<?php
require_once (DIR_SYSTEM . "library/wallee/helper.php");
use \Wallee\Controller\AbstractController;

abstract class ControllerExtensionPaymentWalleeBase extends AbstractController {

	public function index(){
		if (!$this->config->get('wallee_status')) {
			return '';
		}
		$this->load->language('payment/wallee');
		
		$data['configuration_id'] = substr($this->getCode(), strlen('wallee_'));
		
		$data['button_confirm'] = $this->language->get('button_confirm');
		$data['text_loading'] = $this->language->get('text_loading');
		
		$this->load->model('payment/' . $this->getCode());
		$data['text_payment_title'] = $this->{"model_payment_{$this->getCode()}"}->getTitle();
		$data['text_further_details'] = $this->language->get('text_further_details');
		
		$data['opencart_js'] = 'catalog/view/javascript/wallee.js';
		$data['external_js'] = Wallee\Service\Transaction::instance($this->registry)->getJavascriptUrl();
		
		return $this->loadView('payment/wallee/iframe', $data);
	}

	public function confirm(){
		if (!$this->config->get('wallee_status')) {
			return '';
		}
		$result = array(
			'status' => false 
		);
		try {
			$transaction = $this->confirmTransaction();
			$result['status'] = true;
			$result['redirect'] = Wallee\Service\Transaction::instance($this->registry)->getPaymentPageUrl($transaction, $this->getCode());
		}
		catch (Exception $e) {
			\WalleeHelper::instance($this->registry)->dbTransactionRollback();
			\WalleeHelper::instance($this->registry)->log($e->getMessage(), \WalleeHelper::LOG_ERROR);
			$this->load->language('payment/wallee');
			$result['message'] = $this->language->get('error_confirmation'); 
			unset($this->session->data['order_id']); // this order number cannot be used anymore
			Wallee\Service\Transaction::instance($this->registry)->clearTransactionInSession();
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($result));
	}

	private function confirmTransaction(){
		$transaction = Wallee\Service\Transaction::instance($this->registry)->getTransaction($this->getOrderInfo(), false,
				array(
					\Wallee\Sdk\Model\TransactionState::PENDING 
				));
		if ($transaction->getState() == \Wallee\Sdk\Model\TransactionState::PENDING) {
			\WalleeHelper::instance($this->registry)->dbTransactionStart();
			\WalleeHelper::instance($this->registry)->dbTransactionLock($transaction->getLinkedSpaceId(), $transaction->getId());
			Wallee\Service\Transaction::instance($this->registry)->update($this->session->data, true);
			\WalleeHelper::instance($this->registry)->dbTransactionCommit();
			return $transaction;
		}
		
		throw new Exception('Transaction is not pending.');
	}
	
	private function getOrderInfo() {
		if(!isset($this->session->data['order_id'])) {
			throw new Exception("No order_id to confirm.");
		}
		$this->load->model('checkout/order');
		return $this->model_checkout_order->getOrder($this->session->data['order_id']);
	}

	protected function getRequiredPermission(){
		return '';
	}

	protected abstract function getCode();
}