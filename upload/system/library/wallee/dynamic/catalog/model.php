<?php
require_once (DIR_SYSTEM . "library/wallee/helper.php");

abstract class ModelExtensionPaymentWalleeBase extends Model {
	private static $paymentMethods;

	public abstract function getTitle();

	protected abstract function getCode();

	protected abstract function getSortOrder();

	protected abstract function getTerms();

	public function getMethod($address, $total){
		if (!$this->config->get('wallee_status')) {
			return array();
		}
		
		// check if transaction can be saved to the session.
		if (\WalleeHelper::instance($this->registry)->getCustomerSessionIdentifier() === null) {
			return array();
		}
		
		$order_info = array(
			'payment_address' => $address 
		);
		$billing = \WalleeHelper::instance($this->registry)->getAddress('billing');
		$shipping = \WalleeHelper::instance($this->registry)->getAddress('payment', $order_info);
		if (empty($billing) && empty($shipping)) {
			return array();
		}
		
		try {
			if (isset($this->session->data['order_id'])) {
				$transaction = \Wallee\Entity\TransactionInfo::loadByOrderId($this->registry, $this->session->data['order_id']);
				if ($transaction->getTransactionId() &&
						 !in_array($transaction->getState(),
								array(
									\Wallee\Sdk\Model\TransactionState::PENDING,
									\Wallee\Sdk\Model\TransactionState::CREATE 
								))) {
					unset($this->session->data['order_id']);
				}
			}
			
			$available_methods = \Wallee\Service\Transaction::instance($this->registry)->getPaymentMethods($order_info);
			$configuration_id = substr($this->getCode(), strlen('wallee_'));
			
			foreach ($available_methods as $method) {
				if ($method->getId() == $configuration_id) {
					return [
						'title' => $this->getTitle(),
						'code' => $this->getCode(),
						'terms' => $this->getTerms(),
						'sort_order' => $this->getSortOrder() 
					];
				}
			}
		}
		catch (Exception $e) {
		}
		return array();
	}
}