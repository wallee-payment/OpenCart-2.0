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
 * This service provides functions to deal with Wallee voids.
 */
class VoidJob extends AbstractJob {

	public function create(\Wallee\Entity\TransactionInfo $transaction_info){
		try {
			\WalleeHelper::instance($this->registry)->dbTransactionStart();
			\WalleeHelper::instance($this->registry)->dbTransactionLock($transaction_info->getSpaceId(), $transaction_info->getTransactionId());
			
			$job = \Wallee\Entity\VoidJob::loadNotSentForOrder($this->registry, $transaction_info->getOrderId());
			if (!$job->getId()) {
				$job = $this->createBase($transaction_info, $job);
				$job->save();
			}
			
			\WalleeHelper::instance($this->registry)->dbTransactionCommit();
			return $job;
		}
		catch (\Exception $e) {
			\WalleeHelper::instance($this->registry)->dbTransactionRollback();
			throw $e;
		}
	}

	public function send(\Wallee\Entity\VoidJob $job){
		try {
			\WalleeHelper::instance($this->registry)->dbTransactionStart();
			\WalleeHelper::instance($this->registry)->dbTransactionLock($job->getSpaceId(), $job->getTransactionId());
			
			$service = new \Wallee\Sdk\Service\TransactionVoidService(\WalleeHelper::instance($this->registry)->getApiClient());
			$operation = $service->voidOnline($job->getSpaceId(), $job->getTransactionId());
			
			if ($operation->getFailureReason() != null) {
				$job->setFailureReason($operation->getFailureReason()->getDescription());
			}
			
			$labels = array();
			foreach ($operation->getLabels() as $label) {
				$labels[$label->getDescriptor()->getId()] = $label->getContentAsString();
			}
			$job->setLabels($labels);
			
			$job->setJobId($operation->getId());
			$job->setState(\Wallee\Entity\AbstractJob::STATE_SENT);
			$job->save();
			
			\WalleeHelper::instance($this->registry)->dbTransactionCommit();
			return $job;
		}
		catch (\Wallee\Sdk\ApiException $api_exception) {
		}
		catch (\Exception $e) {
			\WalleeHelper::instance($this->registry)->dbTransactionRollback();
			throw $e;
		}
		return $this->handleApiException($hob, $api_exception);
	}
}