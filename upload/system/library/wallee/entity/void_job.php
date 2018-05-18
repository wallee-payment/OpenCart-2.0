<?php

namespace Wallee\Entity;

/**
 *
 */
class VoidJob extends AbstractJob {

	protected static function getTableName(){
		return 'wallee_void_job';
	}
}