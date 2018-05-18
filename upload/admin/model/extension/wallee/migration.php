<?php
require_once modification(DIR_SYSTEM . 'library/wallee/helper.php');

class ModelExtensionWalleeMigration extends Model {
	private static $migrations = array(
		'1.0.0' => array(
			'name' => 'initialize',
			'version' => '1.0.0',
			'function' => 'oc_wallee_update_1_0_0_initialize' 
		) 
	);

	public function migrate(){
		$currentVersion = '0.0.0';
		if ($this->config->has('wallee_migration_version')) {
			$currentVersion = $this->config->get('wallee_migration_version');
		}
		$startingVersion = $currentVersion;
		
		foreach (self::$migrations as $migration) {
			\WalleeHelper::instance($this->registry)->dbTransactionStart();
			try {
				if (version_compare($currentVersion, $migration['version']) === -1) {
					$this->{$migration['function']}();
					\WalleeHelper::instance($this->registry)->dbTransactionCommit();
					$currentVersion = $migration['version'];
				}
			}
			catch (Exception $e) {
				\WalleeHelper::instance($this->registry)->dbTransactionRollback();
				break;
			}
		}
		
		// update version if required
		if (version_compare($startingVersion, $currentVersion) !== 0) {
			$this->load->model('setting/setting');
			$settings = $this->model_setting_setting->getSetting('wallee');
			$settings['wallee_migration_version'] = self::$migrations[$currentVersion]['version'];
			$settings['wallee_migration_name'] = self::$migrations[$currentVersion]['name'];
			$this->model_setting_setting->editSetting('wallee', $settings);
		}
	}

	/**
	 * Purges database & removes all wallee related settings.
	 * Currently leaves modifications alone..
	 */
	public function purge(){
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "wallee_transaction_info` CASCADE;");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "wallee_method_configuration` CASCADE;");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "wallee_completion_job` CASCADE;");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "wallee_refund_job` CASCADE;");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "wallee_void_job` CASCADE;");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "wallee_alert` CASCADE;");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "wallee_shipping_info` CASCADE;");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "wallee_token_info` CASCADE;");
		$this->load->model('setting/setting');
		$settings = $this->model_setting_setting->deleteSetting('wallee');
	}

	private function oc_wallee_update_1_0_0_initialize(){
		$this->db->query(
				"CREATE TABLE `" . DB_PREFIX . "wallee_transaction_info` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`transaction_id` bigint(20) unsigned NOT NULL,
				`state` varchar(255) NOT NULL,
				`space_id` bigint(20) unsigned NOT NULL,
				`space_view_id` bigint(20) unsigned DEFAULT NULL,
				`language` varchar(255) NOT NULL,
				`currency` varchar(255) NOT NULL,
				`created_at` datetime NOT NULL,
				`updated_at` datetime NOT NULL,
				`authorization_amount` decimal(19,8) NOT NULL,
				`image` varchar(512) DEFAULT NULL,
				`labels` longtext,
				`payment_method_id` bigint(20) unsigned DEFAULT NULL,
				`connector_id` bigint(20) unsigned DEFAULT NULL,
				`coupon_code` varchar(20) DEFAULT NULL,
				`order_id` int(11) unsigned NOT NULL,
				`failure_reason` longtext,
				`locked_at` datetime,
				PRIMARY KEY (`id`),
				UNIQUE KEY `unq_transaction_id_space_id` (`transaction_id`,`space_id`),
				UNIQUE KEY `unq_order_id` (`order_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
		
		$this->db->query(
				"CREATE TABLE `" . DB_PREFIX . "wallee_token_info` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`token_id` bigint(20) unsigned NOT NULL,
				`state` varchar(255) NOT NULL,
				`space_id` bigint(20) unsigned NOT NULL,
				`space_view_id` bigint(20) unsigned DEFAULT NULL,
				`created_at` datetime NOT NULL,
				`updated_at` datetime NOT NULL,
				`payment_method_id` bigint(20) unsigned DEFAULT NULL,
				`connector_id` bigint(20) unsigned DEFAULT NULL,
				`customer_id` bigint(20) unsigned DEFAULT NULL,
				`name` varchar(255) NOT NULL,
				`failure_reason` longtext,
				PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
		
		$this->db->query(
				"CREATE TABLE `" . DB_PREFIX . "wallee_method_configuration` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`state` varchar(255) NOT NULL,
				`space_id` bigint(20) unsigned NOT NULL,
				`created_at` datetime NOT NULL,
				`updated_at` datetime NOT NULL,
				`configuration_id` bigint(20) unsigned NOT NULL,
				`configuration_name` varchar(150) NOT NULL,
				`title` longtext,
				`description` longtext,
				`image` varchar(512) DEFAULT NULL,
				`sort_order` int(10),
				PRIMARY KEY (`id`),
				UNIQUE KEY `unq_space_id_configuration_id` (`space_id`,`configuration_id`),
				KEY `idx_space_id` (`space_id`),
				KEY `idx_configuration_id` (`configuration_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
		
		$this->db->query(
				"CREATE TABLE `" . DB_PREFIX . "wallee_completion_job` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`state` varchar(255) NOT NULL,
				`job_id` bigint(20) unsigned,
				`transaction_id` int(10) unsigned NOT NULL,
				`space_id` bigint(20) unsigned NOT NULL,
				`order_id` int(11) unsigned NOT NULL,
				`created_at` datetime NOT NULL,
				`updated_at` datetime NOT NULL,
				`amount` decimal(19,8),
				`labels` longtext,
				`failure_reason` longtext,
				PRIMARY KEY (`id`),
				KEY (`order_id`),
				KEY (`state`),
				KEY `idx_transaction_id_space_id` (`transaction_id`,`space_id`),
				UNIQUE KEY `idx_job_id_space_id_transaction_id` (`job_id`,`space_id`, `transaction_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
		
		$this->db->query(
				"CREATE TABLE `" . DB_PREFIX . "wallee_refund_job` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`job_id` bigint(20) unsigned,
				`state` varchar(255) NOT NULL,
				`external_id` varchar(20) NOT NULL,
				`transaction_id` int(10) unsigned NOT NULL,
				`space_id` bigint(20) unsigned NOT NULL,
				`order_id` int(11) unsigned NOT NULL,
				`created_at` datetime NOT NULL,
				`updated_at` datetime NOT NULL,
				`restock` varchar(1),
				`reduction_items` longtext,
				`amount` decimal(19,8),
				`labels` longtext,
				`failure_reason` longtext,
				PRIMARY KEY (`id`),
				KEY (`order_id`),
				KEY (`state`),
				KEY `idx_transaction_id_space_id` (`transaction_id`,`space_id`),
				UNIQUE KEY `idx_job_id_space_id_transaction_id` (`job_id`,`space_id`, `transaction_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
		
		$this->db->query(
				"CREATE TABLE `" . DB_PREFIX . "wallee_void_job` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`state` varchar(255) NOT NULL,
				`job_id` bigint(20) unsigned,
				`transaction_id` int(10) unsigned NOT NULL,
				`space_id` bigint(20) unsigned NOT NULL,
				`order_id` int(11) unsigned NOT NULL,
				`created_at` datetime NOT NULL,
				`updated_at` datetime NOT NULL,
				`failure_reason` longtext,
				`labels` longtext,
				PRIMARY KEY (`id`),
				KEY (`order_id`),
				KEY (`state`),
				KEY `idx_transaction_id_space_id` (`transaction_id`,`space_id`),
				UNIQUE KEY `idx_job_id_space_id_transaction_id` (`job_id`,`space_id`, `transaction_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
		
		$this->db->query(
				"CREATE TABLE `" . DB_PREFIX . "wallee_shipping_info` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`transaction_id` int(10) unsigned NOT NULL,
				`space_id` bigint(20) unsigned NOT NULL,
				`tax_class_id` int(11) unsigned NOT NULL,
				`cost` decimal(19,8) NOT NULL,
				`created_at` datetime NOT NULL,
				`updated_at` datetime NOT NULL,
				PRIMARY KEY (`id`),
				UNIQUE KEY `idx_transaction_id_space_id` (`transaction_id`,`space_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
		
		$this->db->query(
				"CREATE TABLE `" . DB_PREFIX . "wallee_alert` (
				`id` int(10) NOT NULL AUTO_INCREMENT,
				`key` varchar(20) NOT NULL,
				`count` int unsigned NOT NULL,
				`route` varchar(50) NOT NULL,
				`level` varchar(10) NOT NULL,
				`created_at` datetime NOT NULL,
				`updated_at` datetime NOT NULL,
				PRIMARY KEY (`id`),
				UNIQUE KEY (`key`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
		
		$this->db->query(
				"INSERT INTO `" . DB_PREFIX . "wallee_alert`
				(`key`, `count`, `route`, `level`, `created_at`, `updated_at`) VALUES
				('manual_task', 0, 'extension/wallee/alert/manual', 'danger', NOW(), NOW()),
				('failed_jobs', 0, 'extension/wallee/alert/failed', 'danger', NOW(), NOW());");
		
		$this->db->query(
				"CREATE TABLE `" . DB_PREFIX . "wallee_cron` (
				`id` int(10) NOT NULL AUTO_INCREMENT,
				`security_token` varchar(36) NOT NULL,
				`error_message` varchar(255) NOT NULL,
				`state` varchar(10) NOT NULL,
				`date_scheduled` datetime NOT NULL,
				`date_started` datetime NOT NULL,
				`date_completed` datetime NOT NULL,
				`constraint_key` smallint NOT NULL,
				PRIMARY KEY (`id`),
				KEY (`security_token`),
				UNIQUE KEY (`constraint_key`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
	}
}
