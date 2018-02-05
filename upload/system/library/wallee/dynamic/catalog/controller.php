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
			$service = \Wallee\Service\Transaction::instance($this->registry);
			
			$transaction = $service->getTransaction(array(), false, array(
				\Wallee\Sdk\Model\TransactionState::PENDING 
			));
			if ($transaction->getState() === \Wallee\Sdk\Model\TransactionState::PENDING) {
				\WalleeHelper::instance($this->registry)->dbTransactionStart();
				\WalleeHelper::instance($this->registry)->dbTransactionLock($transaction->getLinkedSpaceId(), $transaction->getId());
				$service->update($this->session->data, true);
				\WalleeHelper::instance($this->registry)->dbTransactionCommit();
				$result['status'] = true;
			}
			else {
				throw new Exception('Transaction is not pending.');
			}
		}
		catch (Exception $e) {
			\WalleeHelper::instance($this->registry)->dbTransactionRollback();
			$result['message'] = $e->getMessage();
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($result));
	}

	protected function getRequiredPermission(){
		return '';
	}

	protected abstract function getCode();
}