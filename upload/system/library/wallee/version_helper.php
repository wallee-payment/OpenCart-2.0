<?php
require_once (DIR_SYSTEM . 'library/wallee/autoload.php');

/**
 * Versioning helper which offers implementations depending on opencart version.
 *
 * @author sebastian
 *
 */
class WalleeVersionHelper {
	const TOKEN = 'token';

	public static function getModifications(){
		return array(
			'WalleeCore' => array(
				'file' => 'WalleeCore.ocmod.xml',
				'default_status' => 1 
			),
			'WalleeAlerts' => array(
				'file' => 'WalleeAlerts.ocmod.xml',
				'default_status' => 1 
			),
			'WalleeAdministration' => array(
				'file' => 'WalleeAdministration.ocmod.xml',
				'default_status' => 1 
			),
			'WalleeQuickCheckoutCompatibility' => array(
				'file' => 'WalleeQuickCheckoutCompatibility.ocmod.xml',
				'default_status' => 0 
			),
			'WalleeXFeeProCompatibility' => array(
				'file' => 'WalleeXFeeProCompatibility.ocmod.xml',
				'default_status' => 0 
			),
			'WalleePreventConfirmationEmail' => array(
				'file' => 'WalleePreventConfirmationEmail.ocmod.xml',
				'default_status' => 0 
			),
			'WalleeEvents' => array(
				'file' => 'WalleeEvents.ocmod.xml',
				'default_status' => 1 
			),
			'WalleeFrontendPdf' => array(
				'file' => 'WalleeFrontendPdf.ocmod.xml',
				'default_status' => 1 
			),
			'WalleeTransactionView' => array(
				'file' => 'WalleeTransactionView.ocmod.xml',
				'default_status' => 1 
			) 
		);
	}

	public static function wrapJobLabels(\Registry $registry, $content){
		return array(
			'title' => $registry->get('language')->get('heading_wallee'),
			'content' => $content 
		);
	}

	public static function getPersistableSetting($value, $default){
		if ($value) {
			$value = $value['value'];
		}
		else {
			$value = $default;
		}
		return $value;
	}

	public static function getTemplate($theme, $template){
	    if (file_exists(DIR_TEMPLATE . $theme . '/template/' . $template . ".tpl")) {
	        return $theme . '/template/' . $template . ".tpl";
	    }
	    else if (file_exists(DIR_TEMPLATE . $template . ".tpl")) {
	    	return $template . ".tpl";
	    }
	    else {	
	        return 'default/template/' . $template . ".tpl";
	    }
	}

	public static function newTax(\Registry $registry){
		return new \Tax($registry);
	}

	public static function getSessionTotals(\Registry $registry){
		// Totals
		$registry->get('load')->model('extension/extension');
		
		$totals = array();
		$taxes = $registry->get('cart')->getTaxes();
		$total = 0;
		
		$sort_order = array();
		
		$results = $registry->get('model_extension_extension')->getExtensions('total');
		
		foreach ($results as $key => $value) {
			$sort_order[$key] = $registry->get('config')->get($value['code'] . '_sort_order');
		}
		
		array_multisort($sort_order, SORT_ASC, $results);
		
		foreach ($results as $result) {
			if ($registry->get('config')->get($result['code'] . '_status')) {
				$registry->get('load')->model('total/' . $result['code']);
				
				// We have to put the totals in an array so that they pass by reference.
				$registry->get('model_total_' . $result['code'])->getTotal($totals, $total, $taxes);
			}
			
			$sort_order = array();
			
			foreach ($totals as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}
			
			array_multisort($sort_order, SORT_ASC, $totals);
		}
		
		return $totals;
	}

	public static function persistPluginStatus(\Registry $registry, array $post){}

	public static function extractPaymentSettingCode($code){
		return $code;
	}

	public static function extractLanguageDirectory($language){
		return $language['directory'];
	}

	public static function createUrl(\Url $url_provider, $route, $query, $ssl){
		if (is_array($query)) {
			$query = http_build_query($query);
		}
		else if (!is_string($query)) {
			throw new Exception("Query must be of type string or array, " . get_class($query) . " given.");
		}
		return $url_provider->link($route, $query, $ssl);
	}

	public static function getOrderTotals($registry){
		// Load total extensions
		$registry->get('load')->model('setting/extension');
		$totalExtensions = $registry->get('model_setting_extension')->getExtensions('total');
		$orderedKeys = array();
		foreach ($totalExtensions as $key => $value) {
			$orderedKeys[$key] = $registry->get('config')->get($value['code'] . '_sort_order');
		}
		array_multisort($orderedKeys, SORT_ASC, $totalExtensions);
		
		$resolvedData = self::buildOrderTotalData($registry, $totalExtensions);
		
		$taxAmounts = $resolvedData['taxAmounts'];
		$totalData = $resolvedData['totalData'];
		
		// Calculate the tax rates (aggregated per position)
		foreach ($totalData as $id => $data) {
			$key = $data['code'];
			$taxRate = 0;
			
			$totalData[$id]['value'] = self::formatAmount($totalData[$id]['value']);
			
			if (isset($taxAmounts[$key]) && $taxAmounts[$key] > 0 && $totalData[$id]['value'] != 0) {
				$taxAmounts[$key] = self::formatAmount($taxAmounts[$key]);
				$taxRate = round(abs($taxAmounts[$key] / $totalData[$id]['value'] * 100), 4);
			}
			$totalData[$id]['tax_rate'] = $taxRate;
		}
		
		return $totalData;
	}

	private function formatAmount(\Registry $registry, $amount){
		//TODO helper / version helper moving
		return $registry->get('currency')->getValue($currency) * $amount;
	}

	private static function getCurrency(\Registry $registry){
		if (isset($registry->get('session')->data['currency'])) {
			return $registry->get('session')->data['currency'];
		}
		return $registry->get('config')->get('config_currency');
	}

	private static function buildOrderTotalData($registry, $totalExtensions){
		$taxAmounts = array();
		$totalData = array();
		$total = 0;
		foreach ($totalExtensions as $extension) {
			if ($registry->get('config')->get($extension['code'] . '_status')) {
				$registry->get('load')->model('total/' . $extension['code']);
				
				$taxes = array();
				$registry->get('model_total_' . $extension['code'])->getTotal($totalData, $total, $taxes);
				$amount = 0;
				
				foreach ($taxes as $value) {
					$amount += $value;
				}
				$taxAmounts[$extension['code']] = $amount;
			}
		}
		return array(
			'taxAmounts' => $taxAmounts,
			'totalData' => $totalData 
		);
	}
}