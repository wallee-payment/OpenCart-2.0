<?php
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