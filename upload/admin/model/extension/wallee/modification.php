<?php
require_once modification(DIR_SYSTEM . 'library/wallee/helper.php');
use Wallee\Model\AbstractModel;

class ModelExtensionWalleeModification extends AbstractModel {
	private static $modifications = array(
		'WalleeCore' => 'WalleeCore.ocmod.xml',
		'WalleeAlerts' => 'WalleeAlerts.ocmod.xml',
		'WalleeLogging' => 'WalleeLogging.ocmod.xml',
		'WalleeQuickCheckoutCompatibility' => 'WalleeQuickCheckoutCompatibility.ocmod.xml',
		'WalleePreventConfirmationEmail' => 'WalleePreventConfirmationEmail.ocmod.xml',
		'WalleeAdministration' => 'WalleeAdministration.ocmod.xml',
		'WalleeFrontendPdf' => 'WalleeFrontendPdf.ocmod.xml',
		'WalleeEvents' => 'WalleeEvents.ocmod.xml' 
	);

	public function install(){
		$path = DIR_SYSTEM . "library/wallee/modification/";
		foreach (self::$modifications as $code => $file) {
			$this->importModification($path . $file);
		}
	}

	public function uninstall(){
		foreach (self::$modifications as $code => $file) {
			$modification_info = $this->getModificationModel()->getModificationByCode($code);
			
			if ($modification_info) {
				$this->getModificationModel()->deleteModification($modification_info['modification_id']);
			}
		}
	}

	private function importModification($file){
		$currentVersion = '0.0.0';
		$xml = file_get_contents($file);
		
		if ($xml) {
			$dom = new DOMDocument('1.0', 'UTF-8');
			$dom->loadXml($xml);
			
			$name = $dom->getElementsByTagName('name')->item(0);
			
			if ($name) {
				$name = $name->nodeValue;
			}
			else {
				$name = '';
			}
			
			$code = $dom->getElementsByTagName('code')->item(0);
			
			if ($code) {
				$code = $code->nodeValue;
				
				// Check to see if the modification is already installed or not.
				$modification_info = $this->getModificationModel()->getModificationByCode($code);
				
				if ($modification_info) {
					$currentVersion = $modification_info['version'];
				}
			}
			else {
				throw new Exception("Could not extract code from modification xml.");
			}
			
			$author = $dom->getElementsByTagName('author')->item(0);
			
			if ($author) {
				$author = $author->nodeValue;
			}
			else {
				$author = '';
			}
			
			$version = $dom->getElementsByTagName('version')->item(0);
			
			if ($version) {
				$version = $version->nodeValue;
			}
			else {
				$version = '';
			}
			
			$link = $dom->getElementsByTagName('link')->item(0);
			
			if ($link) {
				$link = $link->nodeValue;
			}
			else {
				$link = '';
			}
			
			$modification_data = array(
				'name' => $name,
				'code' => $code,
				'author' => $author,
				'version' => $version,
				'link' => $link,
				'xml' => $xml,
				'status' => 1,
				'extension_install_id' => null 
			);
			
			switch (version_compare($currentVersion, $version)) {
				case -1:
					if ($modification_info) {
						$this->getModificationModel()->deleteModification($modification_info['modification_id']);
					}
				case 1:
					$this->getModificationModel()->addModification($modification_data);
					break;
				case 0:
				default:
					break;
			}
		}
	}
}