jQuery(function($) {

	// Hide the success list if the sitemap has never been built.
	if($.trim($('#build-date').text()) !== "Never") {
		$('.tentblogger-seo-sitemap-option').show();
		$('#sitemap_url').show();
	}

	$('#create_sitemap')
		.css('cursor', 'pointer')
		.click(function(evt) {	
		
			var This = this;
			$(This).val('Working...')
			.attr('disabled', true);
			
			var sUrl = location.href + '&tbss=trigger';
			$.get(sUrl, function(data) {
				
				if(data === "fail") {
				
					// show failure messaging and hide success
					$('#last-executed').hide();
					$('#build-date').hide();
					$('#create_sitemap').hide();
					$('#error-message').fadeIn('fast');
					
					$('.tentblogger-seo-sitemap-option').fadeOut(function() {
						$(this).remove();
					});
				
				} else {
				
					$('#build-date')
						.fadeOut('fast', function() {
							$(this)
								.text(data)
								.fadeIn('fast');
						});
						
					$('.tentblogger-seo-sitemap-option').fadeIn('fast');
					$('#sitemap_url').fadeIn('fast');
						
					$(This)	
						.attr('disabled', false)
						.val('Build Your Sitemap');
				
				} // end if/else
				
			});
			
	});
});