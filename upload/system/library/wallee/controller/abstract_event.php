<?php

namespace Wallee\Controller;

abstract class AbstractEvent extends AbstractController {

	protected function validate(){
		$this->language->load('payment/wallee');
		$this->validatePermission();
		// skip valdiating order.
	}
}