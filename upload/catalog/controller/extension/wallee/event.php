<?php
require_once modification(DIR_SYSTEM . 'library/wallee/helper.php');

/**
 * Frontend event hook handler
 * See admin/model/extension/wallee/setup::addEvents
 */
class ControllerExtensionWalleeEvent extends Wallee\Controller\AbstractEvent {

	/**
	 * Adds the wallee device identifier script
	 *
	 * @param string $route
	 * @param array $parameters
	 * @param object $output
	 */
	public function includeDeviceIdentifier(){
		$script = \WalleeHelper::instance($this->registry)->getBaseUrl();
		$script .= '/s/[spaceId]/payment/device.js?sessionIdentifier=[UniqueSessionIdentifier]';
		
		$this->setDeviceCookie();
		
		$script = str_replace(array(
			'[spaceId]',
			'[UniqueSessionIdentifier]' 
		), array(
			$this->config->get('wallee_space_id'),
			$this->request->cookie['wallee_device_id'] 
		), $script);
		
		// async hack
		$script .= '" async="async';
		
		$this->document->addScript($script);
	}

	private function setDeviceCookie(){
		if (isset($this->request->cookie['wallee_device_id'])) {
			$value = $this->request->cookie['wallee_device_id'];
		}
		else {
			$this->request->cookie['wallee_device_id'] = $value = \WalleeHelper::generateUuid();
		}
		setcookie('wallee_device_id', $value, time() + 365 * 24 * 60 * 60, '/');
	}

	/**
	 * Prevent line item changes to authorized wallee transactions.
	 *
	 * @param array $order_info
	 */
	public function canSaveOrder($order_info){
		if (!isset($this->request->get['order_id'])) {
			return;
		}
		
		$order_id = $this->request->get['order_id'];
		$transaction_info = \Wallee\Entity\TransactionInfo::loadByOrderId($this->registry, $order_id);
		
		if ($transaction_info->getId() === null) {
			// not a wallee transaction
			return;
		}
		
		if (\WalleeHelper::isEditableState($transaction_info->getState())) {
			// changing line items still permitted
			return;
		}
		
		$old_order = $this->getOldOrderLineItemData($order_id);
		$new_order = $this->getNewOrderLineItemData($order_info);
		
		foreach ($new_order as $key => $new_item) {
			foreach ($old_order as $old_item) {
				if ($old_item['id'] == $new_item['id'] && \WalleeHelper::instance($this->registry)->areAmountsEqual($old_item['total'],
						$new_item['total'], $transaction_info->getCurrency())) {
					unset($new_order[$key]);
					break;
				}
			}
		}
		
		if (!empty($new_order)) {
			\WalleeHelper::instance($this->registry)->log($this->language->get('error_order_edit') . " ($order_id)",
					\WalleeHelper::LOG_ERROR);
			
			if ($this->request->get['route'] == 'checkout/checkout') {
				// ensure reload without order_id
				unset($this->session->data['order_id']);
				\Wallee\Service\Transaction::instance($this->registry)->waitForStates($this->request->get['order_id'],
						array(
							\Wallee\Sdk\Model\TransactionState::FAILED 
						), 5);
				$transaction_info = \Wallee\Entity\TransactionInfo::loadByOrderId($this->registry, $order_id);
				$this->session->data['error'] = $transaction_info->getFailureReason();
				$this->response->redirect($this->createUrl('checkout/checkout', $this->request->get));
			}
			else {
				$this->language->load('payment/wallee');
				$this->response->addHeader('Content-Type: application/json');
				$this->response->setOutput(json_encode(array(
					'error' => $this->language->get('error_order_edit') 
				)));
				$this->response->output();
				die();
			}
		}
	}

	public function update(){
		try {
			$this->validate();
			$this->validateOrder();
			
			$transaction_info = \Wallee\Entity\TransactionInfo::loadByOrderId($this->registry, $this->request->get['order_id']);
			
			if ($transaction_info->getState() == \Wallee\Sdk\Model\TransactionState::AUTHORIZED) {
				\Wallee\Service\Transaction::instance($this->registry)->updateLineItemsFromOrder($this->request->get['order_id']);
				return;
			}
		}
		catch (\Exception $e) {
		}
	}

	/**
	 * Return simple list of ids and total for the given new order information
	 *
	 * @param array $new_order
	 * @return array
	 */
	private function getNewOrderLineItemData(array $new_order){
		$line_items = array();
		
		foreach ($new_order['products'] as $product) {
			$line_items[] = array(
				'id' => $product['product_id'],
				'total' => $product['total'] 
			);
		}
		
		foreach ($new_order['vouchers'] as $voucher) {
			$line_items[] = array(
				'id' => $voucher['voucher_id'],
				'total' => $voucher['price'] 
			);
		}
		
		foreach ($new_order['totals'] as $total) {
			$line_items[] = array(
				'id' => $total['code'],
				'total' => $total['value'] 
			);
		}
		
		return $line_items;
	}

	/**
	 * Return a simple list of ids and total for the existing order identified by order_id
	 *
	 * @param int $order_id
	 * @return array
	 */
	private function getOldOrderLineItemData($order_id){
		$line_items = array();
		$model = \WalleeHelper::instance($this->registry)->getOrderModel();
		
		foreach ($model->getOrderProducts($order_id) as $product) {
			$line_items[] = array(
				'id' => $product['product_id'],
				'total' => $product['total'] 
			);
		}
		
		foreach ($model->getOrderVouchers($order_id) as $voucher) {
			$line_items[] = array(
				'id' => $voucher['voucher_id'],
				'total' => $voucher['price'] 
			);
		}
		
		foreach ($model->getOrderTotals($order_id) as $total) {
			$line_items[] = array(
				'id' => $total['code'],
				'total' => $total['value'] 
			);
		}
		
		return $line_items;
	}

	protected function getRequiredPermission(){
		return '';
	}
}