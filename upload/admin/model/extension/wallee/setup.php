<?php
require_once modification(DIR_SYSTEM . 'library/wallee/helper.php');
use Wallee\Model\AbstractModel;

class ModelExtensionWalleeSetup extends AbstractModel {

	public function install(){
		$this->load->model("extension/wallee/migration");
		$this->load->model('extension/wallee/modification');
		$this->load->model('extension/wallee/dynamic');
		
		$this->model_extension_wallee_migration->migrate();
		
		try {
			$this->model_extension_wallee_modification->install();
			$this->model_extension_wallee_dynamic->install();
		}
		catch (Exception $e) {
		}
		
		$this->addPermissions();
		$this->addEvents();
	}

	public function synchronize($space_id){
		\WalleeHelper::instance($this->registry)->refreshApiClient();
		\WalleeHelper::instance($this->registry)->refreshWebhook();
		\Wallee\Service\MethodConfiguration::instance($this->registry)->synchronize($space_id);
	}

	public function uninstall($purge = true){
		$this->load->model("extension/wallee/migration");
		$this->load->model('extension/wallee/modification');
		$this->load->model('extension/wallee/dynamic');
		
		$this->model_extension_wallee_dynamic->uninstall();
		if ($purge) {
			$this->model_extension_wallee_migration->purge();
			$this->model_extension_wallee_modification->uninstall();
		}
		
		$this->removeEvents();
		$this->removePermissions();
	}

	private function addEvents(){
		$this->getEventModel()->addEvent('wallee_can_save_order', 'pre.order.edit', 'extension/wallee/event/canSaveOrder');
		$this->getEventModel()->addEvent('wallee_update_items_after_edit', 'post.order.edit', 'extension/wallee/event/update');
		
		//deviceIdentifier, cronScript + refreshWebhook on every page
		// wallee_create_dynamic_files handled via modification: Two refreshs required!
		// wallee_include_device_identifier handled via modification
		// wallee_include_cron_script handled via modification
	}

	private function removeEvents(){
		$this->getEventModel()->deleteEvent('wallee_create_dynamic_files');
		$this->getEventModel()->deleteEvent('wallee_can_save_order');
		$this->getEventModel()->deleteEvent('wallee_update_items_after_edit');
	}

	/**
	 * Adds basic permissions.
	 * Permissions per payment method are added while creating the dynamic files.
	 */
	private function addPermissions(){
		$this->load->model("user/user_group");
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/wallee/event');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/wallee/completion');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/wallee/void');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/wallee/refund');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/wallee/update');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/wallee/pdf');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/wallee/alert');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/wallee/transaction');
	}

	private function removePermissions(){
		$this->load->model("user/user_group");
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/wallee/event');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/wallee/completion');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/wallee/void');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/wallee/refund');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/wallee/update');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/wallee/pdf');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/wallee/alert');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/wallee/transaction');
	}
}