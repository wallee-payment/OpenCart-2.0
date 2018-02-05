(function($s) {
	window.Wallee = {
		handler : null,
		methodConfigurationId : null,
		running : false,

		initialized : function() {
			$('#button-confirm').removeAttr('disabled');
			$('#wallee-iframe-spinner').hide();
			$('#button-confirm').click(function(event) {
				Wallee.handler.validate();
				$('#button-confirm').attr('disabled', 'disabled');
			});
		},

		submit : function() {
			if(!Wallee.running) {
				Wallee.running = true;
				$.getJSON('index.php?route=payment/wallee_'
						+ Wallee.methodConfigurationId + '/confirm', '', function(data,
						status, jqXHR) {
					if (data.status) {
						Wallee.handler.submit();
					} else {
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
			if (typeof window.IframeCheckoutHandler === 'undefined') {
				setTimeout(function() {
					Wallee.init(methodConfigurationId);
				}, 500);
			} else {
				Wallee.methodConfigurationId = methodConfigurationId;
				Wallee.handler = window
						.IframeCheckoutHandler(methodConfigurationId);
				Wallee.handler.setInitializeCallback(this.initialized);
				Wallee.handler.setValidationCallback(this.validated);
				Wallee.handler.create('wallee-iframe-container');
			}
		}
	}
})(jQuery);