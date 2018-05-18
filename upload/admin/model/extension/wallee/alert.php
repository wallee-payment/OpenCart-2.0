<?php
require_once modification(DIR_SYSTEM . 'library/wallee/helper.php');
use Wallee\Model\AbstractModel;

/**
 * Handles the display of alerts in the top right.
 * Is used in combination with
 * - controller/extension/wallee/alert.php
 * - system/library/wallee/modification/WalleeAlerts.ocmod.xml
 */
class ModelExtensionWalleeAlert extends AbstractModel {
	private $alerts;

	public function getAlertsTitle(){
		$this->load->language('payment/wallee');
		return $this->language->get('title_notifications');
	}

	public function getAlerts(){
		if ($this->alerts == null) {
			$this->load->language('payment/wallee');
			$this->alerts = array();
			$alert_entities = \Wallee\Entity\Alert::loadAll($this->registry);
			
			foreach ($alert_entities as $alert_entity) {
				$this->alerts[] = array(
					'url' => $this->createUrl($alert_entity->getRoute(),
							array(
								\WalleeVersionHelper::TOKEN => $this->session->data[\WalleeVersionHelper::TOKEN] 
							)),
					'text' => $this->language->get($alert_entity->getKey()),
					'level' => $alert_entity->getLevel(),
					'count' => $alert_entity->getCount() 
				);
			}
		}
		return $this->alerts;
	}

	public function getAlertCount(){
		$count = 0;
		foreach ($this->getAlerts() as $alert) {
			$count += $alert['count'];
		}
		return $count;
	}
}