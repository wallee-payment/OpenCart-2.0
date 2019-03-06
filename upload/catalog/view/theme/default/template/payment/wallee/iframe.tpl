<div class="panel panel-default">
	<div class="panel-heading">
		<h4><?php echo $text_payment_title; ?></h4>
		<span><?php echo $text_further_details; ?></span>
	</div>
	<div style="padding: 15px;">
		<div id="wallee-iframe-spinner" class="text-center">
			<i style="font-size: 12em;" class='fa fa-spinner fa-spin '></i>
		</div>
		<div id="wallee-iframe-container" class="text-center"
			style="display: none;"></div>

		<div class="buttons" style="overflow: hidden;">
			<div class="pull-right">
				<input type="button" value="<?php echo $button_confirm; ?>"
					id="button-confirm" class="btn btn-primary"
					data-loading-text="<?php echo $text_loading; ?>" disabled />
			</div>
		</div>
	</div>
	<script type="text/javascript" src="<?php echo $external_js; ?>"></script>
	<script type="text/javascript" src="<?php echo $opencart_js; ?>"></script>
	<script type="text/javascript">
    function initWalleeIframe(){
    	if(typeof Wallee === 'undefined') {
    		Window.loadWalleeTimeout = setTimeout(initWalleeIframe, 500);
    	} else {
    		Wallee.init('<?php echo $configuration_id; ?>');
    	}
    }
    if(typeof Window.loadWalleeTimeout !== 'undefined') {
		clearTimeout(Window.loadWalleeTimeout);
    }
    jQuery().ready(initWalleeIframe);
    </script>
</div>