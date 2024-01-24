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

namespace Wallee\Provider;

/**
 * Provider of payment method information from the gateway.
 */
class PaymentMethod extends AbstractProvider {

	protected function __construct(\Registry $registry){
		parent::__construct($registry, 'oc_wallee_payment_methods');
	}

	/**
	 * Returns the payment method by the given id.
	 *
	 * @param int $id
	 * @return \Wallee\Sdk\Model\PaymentMethod
	 */
	public function find($id){
		return parent::find($id);
	}

	/**
	 * Returns a list of payment methods.
	 *
	 * @return \Wallee\Sdk\Model\PaymentMethod[]
	 */
	public function getAll(){
		return parent::getAll();
	}

	protected function fetchData(){
		$method_service = new \Wallee\Sdk\Service\PaymentMethodService(\WalleeHelper::instance($this->registry)->getApiClient());
		return $method_service->all();
	}

	protected function getId($entry){
		/* @var \Wallee\Sdk\Model\PaymentMethod $entry */
		return $entry->getId();
	}
}