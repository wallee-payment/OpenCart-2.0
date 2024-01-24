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

namespace Wallee\Webhook;

/**
 * Webhook processor to handle transaction state transitions.
 */
class Transaction extends AbstractOrderRelated {

	/**
	 *
	 * @see AbstractOrderRelated::load_entity()
	 * @return \Wallee\Sdk\Model\Transaction
	 */
	protected function loadEntity(Request $request){
		$transaction_service = new \Wallee\Sdk\Service\TransactionService(\WalleeHelper::instance($this->registry)->getApiClient());
		return $transaction_service->read($request->getSpaceId(), $request->getEntityId());
	}

	protected function getOrderId($transaction){
		/* @var \Wallee\Sdk\Model\Transaction $transaction */
		return $transaction->getMerchantReference();
	}

	protected function getTransactionId($transaction){
		/* @var \Wallee\Sdk\Model\Transaction $transaction */
		return $transaction->getId();
	}

	protected function processOrderRelatedInner(array $order_info, $transaction){
		/* @var \Wallee\Sdk\Model\Transaction $transaction */
		$transactionInfo = \Wallee\Entity\TransactionInfo::loadByOrderId($this->registry, $order_info['order_id']);

		$finalStates = [
			\Wallee\Sdk\Model\TransactionState::FAILED,
			\Wallee\Sdk\Model\TransactionState::VOIDED,
			\Wallee\Sdk\Model\TransactionState::DECLINE,
			\Wallee\Sdk\Model\TransactionState::FULFILL
		];

		\WalleeHelper::instance($this->registry)->ensurePaymentCode($order_info, $transaction);

		$transactionInfoState = strtoupper($transactionInfo->getState());
		if (!in_array($transactionInfoState, $finalStates)) {
			\Wallee\Service\Transaction::instance($this->registry)->updateTransactionInfo($transaction, $order_info['order_id']);

			switch ($transaction->getState()) {
				case \Wallee\Sdk\Model\TransactionState::CONFIRMED:
					$this->processing($transaction, $order_info);
					break;
				case \Wallee\Sdk\Model\TransactionState::PROCESSING:
					$this->confirm($transaction, $order_info);
					break;
				case \Wallee\Sdk\Model\TransactionState::AUTHORIZED:
					$this->authorize($transaction, $order_info);
					break;
				case \Wallee\Sdk\Model\TransactionState::DECLINE:
					$this->decline($transaction, $order_info);
					break;
				case \Wallee\Sdk\Model\TransactionState::FAILED:
					$this->failed($transaction, $order_info);
					break;
				case \Wallee\Sdk\Model\TransactionState::FULFILL:

					if (!in_array($transactionInfoState, ['AUTHORIZED', 'COMPLETED'])) {
						$this->authorize($transaction, $order_info);
					}
					$this->fulfill($transaction, $order_info);
					break;
				case \Wallee\Sdk\Model\TransactionState::VOIDED:
					$this->voided($transaction, $order_info);
					break;
				case \Wallee\Sdk\Model\TransactionState::COMPLETED:
					$this->waiting($transaction, $order_info);
					break;
				default:
					// Nothing to do.
					break;
			}
		}
	}

	protected function processing(\Wallee\Sdk\Model\Transaction $transaction, array $order_info){
		\WalleeHelper::instance($this->registry)->addOrderHistory($order_info['order_id'], 'wallee_processing_status_id',
				\WalleeHelper::instance($this->registry)->getTranslation('message_webhook_processing'));
	}

	protected function confirm(\Wallee\Sdk\Model\Transaction $transaction, array $order_info){
		\WalleeHelper::instance($this->registry)->addOrderHistory($order_info['order_id'], 'wallee_processing_status_id',
				\WalleeHelper::instance($this->registry)->getTranslation('message_webhook_confirm'));
	}

	protected function authorize(\Wallee\Sdk\Model\Transaction $transaction, array $order_info){
		\WalleeHelper::instance($this->registry)->addOrderHistory($order_info['order_id'], 'wallee_authorized_status_id',
				\WalleeHelper::instance($this->registry)->getTranslation('message_webhook_authorize'));
	}

	protected function waiting(\Wallee\Sdk\Model\Transaction $transaction, array $order_info){
		\WalleeHelper::instance($this->registry)->addOrderHistory($order_info['order_id'], 'wallee_completed_status_id',
				\WalleeHelper::instance($this->registry)->getTranslation('message_webhook_waiting'));
	}

	protected function decline(\Wallee\Sdk\Model\Transaction $transaction, array $order_info){
		\WalleeHelper::instance($this->registry)->addOrderHistory($order_info['order_id'], 'wallee_decline_status_id',
				\WalleeHelper::instance($this->registry)->getTranslation('message_webhook_decline'));
	}

	protected function failed(\Wallee\Sdk\Model\Transaction $transaction, array $order_info){
		\WalleeHelper::instance($this->registry)->addOrderHistory($order_info['order_id'], 'wallee_failed_status_id',
				\WalleeHelper::instance($this->registry)->getTranslation('message_webhook_failed'));
	}

	protected function fulfill(\Wallee\Sdk\Model\Transaction $transaction, array $order_info){
		\WalleeHelper::instance($this->registry)->addOrderHistory($order_info['order_id'], 'wallee_fulfill_status_id',
				\WalleeHelper::instance($this->registry)->getTranslation('message_webhook_fulfill'));
	}

	protected function voided(\Wallee\Sdk\Model\Transaction $transaction, array $order_info){
		\WalleeHelper::instance($this->registry)->addOrderHistory($order_info['order_id'], 'wallee_voided_status_id',
				\WalleeHelper::instance($this->registry)->getTranslation('message_webhook_voided'));
	}
}