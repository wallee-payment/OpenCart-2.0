<?php

namespace Wallee\Controller;

/**
 * Base controller class offering a validate method and wrappers for functions which may differ between versions (redirect, link etc.)
 *
 * The validate method checks if permissions are set (if in admin)
 * If the order id is set in the request->get[] array,
 * If the order id is part of a wallee transaction,
 * If the user (non admin) is the owner of the given order.
 */
abstract class AbstractController extends \Controller {

	protected function loadView($template, $data = array()){
		$template .= '.tpl';
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/' . $template)) {
			return $this->load->view($this->config->get('config_template') . '/template/' . $template, $data);
		}
		else {
			return $this->load->view($template, $data);
		}
	}

	protected function validate(){
		$this->language->load('payment/wallee');
		$this->validatePermission();
		$this->validateOrder();
	}

	protected function validatePermission(){
		if (\WalleeHelper::instance($this->registry)->isAdmin()) {
			if (!$this->user->hasPermission('access', $this->getRequiredPermission())) {
				throw new \Exception($this->language->get('error_permission'));
			}
		}
	}

	protected function displayError($message){
		$variables = $this->getAdminSurroundingTemplates();
		$variables['text_error'] = $message;
		$this->response->setOutput($this->loadView("extension/wallee/error", $variables));
	}

	protected function getAdminSurroundingTemplates(){
		return array(
			'header' => $this->load->controller("common/header"),
			'column_left' => $this->load->controller("common/column_left"),
			'footer' => $this->load->controller("common/footer") 
		);
	}

	protected function validateOrder(){
		if (!isset($this->request->get['order_id'])) {
			throw new \Exception($this->language->get('error_order_id'));
		}
		if (!\WalleeHelper::instance($this->registry)->isValidOrder($this->request->get['order_id'])) {
			throw new \Exception($this->language->get('error_not_wallee'));
		}
	}

	protected function createUrl($route, $query){
		return \WalleeHelper::createUrl($this->url, $route, $query, $this->config->get('config_secure'));
	}

	protected abstract function getRequiredPermission();
}