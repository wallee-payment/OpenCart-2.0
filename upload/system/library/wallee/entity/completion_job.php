<?php

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