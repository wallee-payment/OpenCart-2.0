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

class ControllerExtensionWalleePdf extends Wallee\Controller\AbstractPdf {

	public function packingSlip(){
		$this->validate();
		$this->downloadPackingSlip($this->request->get['order_id']);
	}

	public function invoice(){
		$this->validate();
		$this->downloadInvoice($this->request->get['order_id']);
	}

	
	protected function getRequiredPermission(){
		return '';
	}
}