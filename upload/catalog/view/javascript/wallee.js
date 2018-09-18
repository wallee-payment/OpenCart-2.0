(function($) {
	window.Wallee = {
		handler : null,
		methodConfigurationId : null,
		running : false,
		initCalls : 0,
		initMaxCalls : 10,

		initialized : function() {
			$('#button-confirm').removeAttr('disabled');
			$('#wallee-iframe-spinner').hide();
			$('#wallee-iframe-container').show();
			$('#button-confirm').click(function(event) {
				Wallee.handler.validate();
				$('#button-confirm').attr('disabled', 'disabled');
			});
		},

		fallback : function(methodConfigurationId) {
			Wallee.methodConfigurationId = methodConfigurationId;
			$('#button-confirm').click(Wallee.submit);
			$('#button-confirm').removeAttr('disabled');
			$('#wallee-iframe-spinner').toggle();
		},

		submit : function() {
			if (!Wallee.running) {
				Wallee.running = true;
				$.getJSON('index.php?route=payment/wallee_'
						+ Wallee.methodConfigurationId
						+ '/confirm', '', function(data, status, jqXHR) {
					if (data.status) {
						if(Wallee.handler) {
							Wallee.handler.submit();
						}
						else {
							window.location.assign(data.redirect);
						}
					}
					else {
						alert(data.message);
						$('#button-confirm').removeAttr('disabled');
					}
					Wallee.running = false;
				});
			}
		},

		validated : function(result) {
			if (result.success) {
				Wallee.submit();
			} else {
				$('#button-confirm').removeAttr('disabled');
			}
		},

		init : function(methodConfigurationId) {
			Wallee.initCalls++;
			if (typeof window.IframeCheckoutHandler === 'undefined') {
				if (Wallee.initCalls < Wallee.initMaxCalls) {
					setTimeout(function() {
						Wallee.init(methodConfigurationId);
					}, 500);
				} else {
					Wallee.fallback(methodConfigurationId);
				}
			} else {
				Wallee.methodConfigurationId = methodConfigurationId;
				Wallee.handler = window
						.IframeCheckoutHandler(methodConfigurationId);
				Wallee.handler
						.setInitializeCallback(this.initialized);
				Wallee.handler
						.setValidationCallback(this.validated);
				Wallee.handler
						.create('wallee-iframe-container');
			}
		}
	}
})(jQuery);