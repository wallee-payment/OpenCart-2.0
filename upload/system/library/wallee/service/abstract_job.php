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

namespace Wallee\Service;

/**
 * This service provides functions to deal with jobs, including locking and setting states.
 */
abstract class AbstractJob extends AbstractService {

	/**
	 * Set the state of the given job to failed with the message of the api exception.
	 * Expects a database transaction to be running, and will commit / rollback depending on outcome.
	 * 
	 * @param \Wallee\Entity\AbstractJob $job
	 * @param \Wallee\Sdk\ApiException $api_exception
	 * @throws \Exception
	 * @return \Wallee\Service\AbstractJob
	 */
	protected function handleApiException(\Wallee\Entity\AbstractJob $job, \Wallee\Sdk\ApiException $api_exception){
		try {
			$job->setState(\Wallee\Entity\AbstractJob::STATE_FAILED_CHECK);
			$job->setFailureReason([
				\WalleeHelper::FALLBACK_LANGUAGE => $api_exception->getMessage() 
			]);
			$job->save();
			\WalleeHelper::instance($this->registry)->dbTransactionCommit();
			return $job;
		}
		catch (\Exception $e) {
			\WalleeHelper::instance($this->registry)->dbTransactionRollback();
			throw new \Exception($e->getMessage() . ' | ' . $api_exception->getMessage(), $e->getCode(), $api_exception);
		}
	}

	protected function createBase(\Wallee\Entity\TransactionInfo $transaction_info, \Wallee\Entity\AbstractJob $job){
		$job->setTransactionId($transaction_info->getTransactionId());
		$job->setOrderId($transaction_info->getOrderId());
		$job->setSpaceId($transaction_info->getSpaceId());
		$job->setState(\Wallee\Entity\AbstractJob::STATE_CREATED);
		
		return $job;
	}
}