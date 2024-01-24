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

namespace Wallee\Controller;

abstract class AbstractEvent extends AbstractController {

	protected function validate(){
		$this->language->load('payment/wallee');
		$this->validatePermission();
		// skip valdiating order.
	}
}