<?php
require_once modification(DIR_SYSTEM . 'library/wallee/helper.php');

/**
 * Handles the display of alerts in the top right.
 * Is used in combination with
 * - model/extension/wallee/alert.php
 * - system/library/wallee/modification/WalleeAlerts.ocmod.xml
 */
class ControllerExtensionWalleeAlert extends Wallee\Controller\AbstractEvent {

	/**
	 * Redirects the user to the manual task overview in the wallee backend.
	 */
	public function manual(){
		try {
			$this->validate();
			$this->response->redirect(\WalleeHelper::getBaseUrl() . '/s/' . $this->config->get('wallee_space_id') . '/manual-task/list');
		}
		catch (Exception $e) {
			$this->displayError($e->getMessage());
		}
	}

	/**
	 * Redirect the user to the order with the oldest checkable failed job.
	 */
	public function failed(){
		try {
			$oldest_failed = \Wallee\Entity\RefundJob::loadOldestCheckable($this->registry);
			if (!$oldest_failed->getId()) {
				$oldest_failed = \Wallee\Entity\CompletionJob::loadOldestCheckable($this->registry);
			}
			if (!$oldest_failed->getId()) {
				$oldest_failed = \Wallee\Entity\VoidJob::loadOldestCheckable($this->registry);
			}
			$this->response->redirect(
					$this->createUrl('sale/order/info',
							array(
								\WalleeHelper::TOKEN => $this->session->data[\WalleeHelper::TOKEN],
								'order_id' => $oldest_failed->getOrderId() 
							)));
		}
		catch (Exception $e) {
			$this->displayError($e->getMessage());
		}
	}

	protected function getRequiredPermission(){
		return 'extension/wallee/alert';
	}
}