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

namespace Wallee\Entity;

/**
 *
 * @method void setAmount(float $amount)
 * @method float getAmount()
 *
 */
class CompletionJob extends AbstractJob {

	protected static function getFieldDefinition(){
		return array_merge(parent::getFieldDefinition(), [
			'amount' => ResourceType::DECIMAL 
		]);
	}

	protected static function getTableName(){
		return 'wallee_completion_job';
	}
}