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
use Wallee\Model\AbstractModel;

/**
 * Handles the customer order info.
 */
class ModelExtensionWalleeOrder extends AbstractModel {

	public function getButtons($order_id){
		if (!\WalleeHelper::instance($this->registry)->isValidOrder($order_id)) {
			return array();
		}
		
		$this->language->load('payment/wallee');
		$transaction_info = \Wallee\Entity\TransactionInfo::loadByOrderId($this->registry, $order_id);
		
		$buttons = array();
		
		if ($this->config->get('wallee_download_packaging') && $transaction_info->getState() == \Wallee\Sdk\Model\TransactionState::FULFILL) {
			$buttons[] = $this->getPackagingButton();
		}
		
		if ($this->config->get('wallee_download_invoice') && in_array($transaction_info->getState(),
				array(
					\Wallee\Sdk\Model\TransactionState::FULFILL,
					\Wallee\Sdk\Model\TransactionState::COMPLETED,
					\Wallee\Sdk\Model\TransactionState::DECLINE 
				))) {
			$buttons[] = $this->getInvoiceButton();
		}
		
		return $buttons;
	}

	private function getInvoiceButton(){
		return array(
			'text' => $this->language->get('button_invoice'),
			'icon' => 'download',
			'url' => $this->createUrl('extension/wallee/pdf/invoice', array(
				'order_id' => $this->request->get['order_id'] 
			)) 
		);
	}

	private function getPackagingButton(){
		return array(
			'text' => $this->language->get('button_packing_slip'),
			'icon' => 'download',
			'url' => $this->createUrl('extension/wallee/pdf/packingSlip', array(
				'order_id' => $this->request->get['order_id'] 
			)) 
		);
	}
}