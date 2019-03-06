(function($) {
	window.Wallee = {
		handler : null,
		methodConfigurationId : null,
		running : false,
		initCalls : 0,
		initMaxCalls : 10,
		confirmationButtonSources: ['#button-confirm', '#journal-checkout-confirm-button'],

		initialized : function() {
			$('#wallee-iframe-spinner').hide();
			$('#wallee-iframe-container').show();
			Wallee.enableConfirmButton();
			$('#button-confirm').click(function(event) {
				Wallee.handler.validate();
				Wallee.disableConfirmButton();
			});
		},

		fallback : function(methodConfigurationId) {
			Wallee.methodConfigurationId = methodConfigurationId;
			$('#button-confirm').click(Wallee.submit);
			$('#wallee-iframe-spinner').toggle();
			Wallee.enableConfirmButton();
		},
		
		reenable: function() {
			Wallee.enableConfirmButton();
			if($('html').hasClass('quick-checkout-page')) { // modifications do not work for js
				triggerLoadingOff();
			}
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
						Wallee.reenable();
					}
					Wallee.running = false;
				});
			}
		},

		validated : function(result) {
			if (result.success) {
				Wallee.submit();
			} else {
				Wallee.reenable();
			}
		},

		init : function(methodConfigurationId) {
			Wallee.initCalls++;
			Wallee.disableConfirmButton();
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
					.setEnableSubmitCallback(this.enableConfirmButton);
				Wallee.handler
					.setDisableSubmitCallback(this.disableConfirmButton);
				Wallee.handler
						.create('wallee-iframe-container');
			}
		},
		
		enableConfirmButton : function() {
			for(var i = 0; i < Wallee.confirmationButtonSources.length; i++) {
				var button = $(Wallee.confirmationButtonSources[i]);
				if(button.length) {
					button.removeAttr('disabled');
				}
			}
		},
		
		disableConfirmButton : function() {
			for(var i = 0; i < Wallee.confirmationButtonSources.length; i++) {
				var button = $(Wallee.confirmationButtonSources[i]);
				if(button.length) {
					button.attr('disabled', 'disabled');
				}
			}
		}
	}
})(jQuery);