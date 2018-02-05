<?php

namespace Wallee\Model;

abstract class AbstractModel extends \Model {
	private $event_model;
	private $extension_model;
	private $modification_model;

	protected function createUrl($route, $query, $ssl = 'SSL'){
		return \WalleeHelper::createUrl($this->url, $route, $query, $ssl);
	}

	protected function getEventModel(){
		if ($this->event_model == null) {
			$this->loadEventModel();
		}
		return $this->event_model;
	}

	protected function getExtensionModel(){
		if ($this->extension_model == null) {
			$this->loadExtensionModel();
		}
		return $this->extension_model;
	}

	protected function getModificationModel(){
		if ($this->modification_model == null) {
			$this->loadModificationModel();
		}
		return $this->modification_model;
	}

	private function loadModificationModel(){
		$this->load->model('extension/modification');
		$this->modification_model = $this->model_extension_modification;
	}

	private function loadExtensionModel(){
		$this->load->model('extension/extension');
		$this->extension_model = $this->model_extension_extension;
	}

	private function loadEventModel(){
		$this->load->model('extension/event');
		$this->event_model = $this->model_extension_event;
	}
}
