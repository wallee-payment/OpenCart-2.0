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
 * Provider of label descriptor information from the gateway.
 */
class LabelDescriptor extends AbstractProvider {

	protected function __construct(\Registry $registry){
		parent::__construct($registry, 'oc_wallee_label_descriptor');
	}

	/**
	 * Returns the label descriptor by the given code.
	 *
	 * @param int $id
	 * @return \Wallee\Sdk\Model\LabelDescriptor
	 */
	public function find($id){
		return parent::find($id);
	}

	/**
	 * Returns a list of label descriptors.
	 *
	 * @return \Wallee\Sdk\Model\LabelDescriptor[]
	 */
	public function getAll(){
		return parent::getAll();
	}

	protected function fetchData(){
		$label_descriptor_service = new \Wallee\Sdk\Service\LabelDescriptionService(\WalleeHelper::instance($this->registry)->getApiClient());
		return $label_descriptor_service->all();
	}

	protected function getId($entry){
		/* @var \Wallee\Sdk\Model\LabelDescriptor $entry */
		return $entry->getId();
	}
}