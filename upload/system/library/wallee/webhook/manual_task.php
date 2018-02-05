<?php

namespace Wallee\Webhook;

/**
 * Webhook processor to handle manual task state transitions.
 */
class ManualTask extends AbstractWebhook {

	/**
	 * Updates the number of open manual tasks.
	 *
	 * @param \Wallee\Webhook\Request $request
	 */
	public function process(Request $request){
		$manual_task_service = \Wallee\service\ManualTask::instance($this->registry);
		$manual_task_service->update();
	}
}