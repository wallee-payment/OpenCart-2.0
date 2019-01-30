<?php
require_once modification(DIR_SYSTEM . 'library/wallee/helper.php');
use Wallee\Model\AbstractModel;
use Wallee\Entity\TransactionInfo;
use Wallee\Provider\PaymentMethod;

class ModelExtensionWalleeTransaction extends AbstractModel {
	const DATE_FORMAT = 'Y-m-d H:i:s';

	public function loadList(array $filters){
		$transactionInfoList = TransactionInfo::loadByFilters($this->registry, $filters);
		/* @var $transactionInfoList TransactionInfo[] */
		$transactions = array();
		foreach ($transactionInfoList as $transactionInfo) {
			$paymentMethod = PaymentMethod::instance($this->registry)->find($transactionInfo->getPaymentMethodId());
			if ($paymentMethod) {
				$paymentMethodName = WalleeHelper::instance($this->registry)->translate($paymentMethod->getName()) . " (" . $transactionInfo->getPaymentMethodId() . ")";
			}
			else {
				$paymentMethodName = $transactionInfo->getPaymentMethodId();
			}
			$transactions[] = array(
				'id' => $transactionInfo->getId(),
				'order_id' => $transactionInfo->getOrderId(),
				'transaction_id' => $transactionInfo->getTransactionId(),
				'space_id' => $transactionInfo->getSpaceId(),
				'space_view_id' => $transactionInfo->getSpaceViewId(),
				'state' => $transactionInfo->getState(),
				'authorization_amount' => $transactionInfo->getAuthorizationAmount(),
				'created_at' => $transactionInfo->getCreatedAt()->format(self::DATE_FORMAT),
				'updated_at' => $transactionInfo->getUpdatedAt()->format(self::DATE_FORMAT),
				'payment_method' => $paymentMethodName,
				'view' => WalleeVersionHelper::createUrl($this->url, 'sale/order/info',
						array(
							'token' => $this->session->data['token'],
							'order_id' => $transactionInfo->getOrderId() 
						), true) 
			);
		}
		return $transactions;
	}
	
	public function getOrderStatuses() {
		return array(
			'',
			Wallee\Sdk\Model\TransactionState::AUTHORIZED,
			Wallee\Sdk\Model\TransactionState::COMPLETED,
			Wallee\Sdk\Model\TransactionState::CONFIRMED,
			Wallee\Sdk\Model\TransactionState::CREATE,
			Wallee\Sdk\Model\TransactionState::DECLINE,
			Wallee\Sdk\Model\TransactionState::FULFILL,
			Wallee\Sdk\Model\TransactionState::FAILED,
			Wallee\Sdk\Model\TransactionState::PENDING,
			Wallee\Sdk\Model\TransactionState::PROCESSING,
			Wallee\Sdk\Model\TransactionState::AUTHORIZED,
			Wallee\Sdk\Model\TransactionState::VOIDED,
		);
	}
	
	public function countRows() {
		return TransactionInfo::countRows($this->registry);
	}
}