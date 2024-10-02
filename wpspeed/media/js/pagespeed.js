/**
 * PageSpeed class
 * 
 * @package JSPEED::plugins::system
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
//'use strict';
(function($) {
	var PageSpeed = function() {
		/**
		 * Current active data sources for sitemap genaration
		 * The first async call to ajaxserver is mean to grab the full list
		 * of published data sources to process
		 * 
		 * @access private
		 * @var Array
		 */
		var sessionAPIData;
		
		/**
		 * Build the PageSpeed cards interface performing the API request
		 * 
		 * @access private
		 * @return Void
		 */
    	var buildPageSpeedInterface = function(data) {
    		$('div.pagespeed-test-url').text(data.analyzedUrl).addClass('pagespeed-test-url-populated');
    		
    		var pageSpeedScoreDesktop = data.pagespeedDesktop;
    		var pageSpeedScoreMobile = data.pagespeedMobile;
    
    		if(pageSpeedScoreDesktop >= 0 && pageSpeedScoreDesktop <= 49) {
        		var desktopColor = '#CC0000';
    			var desktopBgColor = '#ffebeb';
        	}
        	if(pageSpeedScoreDesktop >= 50 && pageSpeedScoreDesktop <= 89) {
        		var desktopColor = '#FFAA33';
    			var desktopBgColor = '#fff7eb';
        	}
        	if(pageSpeedScoreDesktop >= 90) {
        		var desktopColor = '#008000';
    			var desktopBgColor = '#e6faf0';
        	}
    
    		if(pageSpeedScoreMobile >= 0 && pageSpeedScoreMobile <= 49) {
        		var mobileColor = '#CC0000';
    			var mobileBgColor = '#ffebeb';
        	}
        	if(pageSpeedScoreMobile >= 50 && pageSpeedScoreMobile <= 89) {
        		var mobileColor = '#FFAA33';
    			var mobileBgColor = '#fff7eb';
        	}
        	if(pageSpeedScoreMobile >= 90) {
        		var mobileColor = '#008000';
    			var mobileBgColor = '#e6faf0';
        	}
    
    		// Build the PageSpeed interface
    		$('div.timeline-item').empty();
    		$('#timeline-mobile div.timeline-item').append('<div class="row row-pagespeed row-pagespeed-header pt-2 pb-2 jspeed-setting-row border-bottom"><div class="col p-0 col-lg-12 text-center"><span class="fas fa-mobile-alt" aria-hidden="true"></span> Mobile</div></div>');
    		$('#timeline-desktop div.timeline-item').append('<div class="row row-pagespeed row-pagespeed-header pt-2 pb-2 jspeed-setting-row border-bottom"><div class="col p-0 col-lg-12 text-center"><span class="icon-desktop" aria-hidden="true"></span> Desktop</div></div>');
    		$('div.timeline-item').append('<progress-ring stroke="8" radius="60" color="#CC0000" progress="0"></progress-ring>');
    
    		var desktopRing = document.querySelector('#timeline-desktop progress-ring');
    		desktopRing.setAttribute('progress', pageSpeedScoreDesktop);
    		desktopRing.setAttribute('color', desktopColor);
    		desktopRing.setAttribute('bgcolor', desktopBgColor);
    
    		var iconSymbol = '';
    		var fcpDesktopLabel = '';
    		var fcpDesktopFloat = parseFloat(data.fcpDesktop);
    		switch(true) {
    			case (fcpDesktopFloat >= 0 && fcpDesktopFloat <= 1.8):
    				fcpDesktopLabel = 'bg-success';
    				iconSymbol = 'fas fa-circle';
    			break;
    
    			case (fcpDesktopFloat > 1.8 && fcpDesktopFloat <= 3):
    				fcpDesktopLabel = 'bg-warning';
    				iconSymbol = 'fas fa-square';
    			break;
    
    			case (fcpDesktopFloat > 3):
    				fcpDesktopLabel = 'bg-danger';
    				iconSymbol = 'fas fa-exclamation-triangle';
    			break;
    		}
    		$('#timeline-desktop div.timeline-item').append('<div class="row row-pagespeed pt-2 pb-2 jspeed-setting-row border-bottom">' +
    														  '<div class="col p-0 col-lg-8 text-left"><span class="' + iconSymbol + '" aria-hidden="true"></span> First Contentful Paint</div>' +
    														  '<div class="col p-0 col-lg-4 text-center"><span class="badge ' + fcpDesktopLabel + '">' + fcpDesktopFloat + ' s</span></div>' +
    													  	'</div>');
    		var siDesktopLabel = '';
    		var siDesktopFloat = parseFloat(data.siDesktop);
    		switch(true) {
    			case (siDesktopFloat >= 0 && siDesktopFloat <= 3.4):
    				siDesktopLabel = 'bg-success';
    				iconSymbol = 'fas fa-circle';
    			break;
    
    			case (siDesktopFloat > 3.4 && siDesktopFloat <= 5.8):
    				siDesktopLabel = 'bg-warning';
    				iconSymbol = 'fas fa-square';
    			break;
    
    			case (siDesktopFloat > 5.8):
    				siDesktopLabel = 'bg-danger';
    				iconSymbol = 'fas fa-exclamation-triangle';
    			break;
    		}
    		$('#timeline-desktop div.timeline-item').append('<div class="row row-pagespeed pt-2 pb-2 jspeed-setting-row border-bottom">' +
    														  '<div class="col p-0 col-lg-8 text-left"><span class="' + iconSymbol + '" aria-hidden="true"></span> Speed Index</div>' +
    														  '<div class="col p-0 col-lg-4 text-center"><span class="badge ' + siDesktopLabel + '">' + siDesktopFloat + ' s</span></div>' +
    													  	'</div>');
    
    		var lcpDesktopLabel = '';
    		var lcpDesktopFloat = parseFloat(data.lcpDesktop);
    		switch(true) {
    			case (lcpDesktopFloat >= 0 && lcpDesktopFloat <= 2.5):
    				lcpDesktopLabel = 'bg-success';
    				iconSymbol = 'fas fa-circle';
    			break;
    
    			case (lcpDesktopFloat > 2.5 && lcpDesktopFloat <= 4):
    				lcpDesktopLabel = 'bg-warning';
    				iconSymbol = 'fas fa-square';
    			break;
    
    			case (lcpDesktopFloat > 4):
    				lcpDesktopLabel = 'bg-danger';
    				iconSymbol = 'fas fa-exclamation-triangle';
    			break;
    		}
    		$('#timeline-desktop div.timeline-item').append('<div class="row row-pagespeed pt-2 pb-2 jspeed-setting-row border-bottom">' +
    														  '<div class="col p-0 col-lg-8 text-left"><span class="' + iconSymbol + '" aria-hidden="true"></span> Largest Contentful Paint</div>' +
    														  '<div class="col p-0 col-lg-4 text-center"><span class="badge ' + lcpDesktopLabel + '">' + lcpDesktopFloat + ' s</span></div>' +
    													  	'</div>');
    
    		var interactiveDesktopLabel = '';
    		var interactiveDesktopFloat = parseFloat(data.interactiveDesktop);
    		switch(true) {
    			case (interactiveDesktopFloat >= 0 && interactiveDesktopFloat <= 3.8):
    				interactiveDesktopLabel = 'bg-success';
    				iconSymbol = 'fas fa-circle';
    			break;
    
    			case (interactiveDesktopFloat > 3.8 && interactiveDesktopFloat <= 7.3):
    				interactiveDesktopLabel = 'bg-warning';
    				iconSymbol = 'fas fa-square';
    			break;
    
    			case (interactiveDesktopFloat > 7.3):
    				interactiveDesktopLabel = 'bg-danger';
    				iconSymbol = 'fas fa-exclamation-triangle';
    			break;
    		}
    		$('#timeline-desktop div.timeline-item').append('<div class="row row-pagespeed pt-2 pb-2 jspeed-setting-row border-bottom">' +
    														  '<div class="col p-0 col-lg-8 text-left"><span class="' + iconSymbol + '" aria-hidden="true"></span> Time To Interactive</div>' +
    														  '<div class="col p-0 col-lg-4 text-center"><span class="badge ' + interactiveDesktopLabel + '">' + interactiveDesktopFloat + ' s</span></div>' +
    													  	'</div>');
    
    		var tbtDesktopLabel = '';
    		var tbtDesktopInt = parseInt(data.tbtDesktop);
    		switch(true) {
    			case (tbtDesktopInt >= 0 && tbtDesktopInt <= 200):
    				tbtDesktopLabel = 'bg-success';
    				iconSymbol = 'fas fa-circle';
    			break;
    
    			case (tbtDesktopInt > 200 && tbtDesktopInt <= 600):
    				tbtDesktopLabel = 'bg-warning';
    				iconSymbol = 'fas fa-square';
    			break;
    
    			case (tbtDesktopInt > 600):
    				tbtDesktopLabel = 'bg-danger';
    				iconSymbol = 'fas fa-exclamation-triangle';
    			break;
    		}
    		$('#timeline-desktop div.timeline-item').append('<div class="row row-pagespeed pt-2 pb-2 jspeed-setting-row border-bottom">' +
    														  '<div class="col p-0 col-lg-8 text-left"><span class="' + iconSymbol + '" aria-hidden="true"></span> Total Blocking Time</div>' +
    														  '<div class="col p-0 col-lg-4 text-center"><span class="badge ' + tbtDesktopLabel + '">' + tbtDesktopInt + ' ms</span></div>' +
    													  	'</div>');
    
    		var clsDesktopLabel = '';
    		var clsDesktopFloat = parseFloat(data.clsDesktop);
    		switch(true) {
    			case (clsDesktopFloat >= 0 && clsDesktopFloat <= 0.1):
    				clsDesktopLabel = 'bg-success';
    				iconSymbol = 'fas fa-circle';
    			break;
    
    			case (clsDesktopFloat > 0.1 && clsDesktopFloat <= 0.25):
    				clsDesktopLabel = 'bg-warning';
    				iconSymbol = 'fas fa-square';
    			break;
    
    			case (clsDesktopFloat > 0.25):
    				clsDesktopLabel = 'bg-danger';
    				iconSymbol = 'fas fa-exclamation-triangle';
    			break;
    		}
    		$('#timeline-desktop div.timeline-item').append('<div class="row row-pagespeed pt-2 pb-2 jspeed-setting-row border-bottom">' +
    														  '<div class="col p-0 col-lg-8 text-left"><span class="' + iconSymbol + '" aria-hidden="true"></span> Cumulative Layout Shift</div>' +
    														  '<div class="col p-0 col-lg-4 text-center"><span class="badge ' + clsDesktopLabel + '">' + clsDesktopFloat + '</span></div>' +
    													  	'</div>');
    		
    		$('#timeline-desktop div.timeline-item').append('<img class="jspeed-screenshot" alt="PageSpeed Screenshot" src="' + data.screenShotDesktop + '"/>');
    
    		var mobileRing = document.querySelector('#timeline-mobile progress-ring');
    		mobileRing.setAttribute('progress', pageSpeedScoreMobile);
    		mobileRing.setAttribute('color', mobileColor);
    		mobileRing.setAttribute('bgcolor', mobileBgColor);
    
    		var fcpMobileLabel = '';
    		var fcpMobileFloat = parseFloat(data.fcpMobile);
    		switch(true) {
    			case (fcpMobileFloat >= 0 && fcpMobileFloat <= 1.8):
    				fcpMobileLabel = 'bg-success';
    				iconSymbol = 'fas fa-circle';
    			break;
    
    			case (fcpMobileFloat > 1.8 && fcpMobileFloat <= 3):
    				fcpMobileLabel = 'bg-warning';
    				iconSymbol = 'fas fa-square';
    			break;
    
    			case (fcpMobileFloat > 3):
    				fcpMobileLabel = 'bg-danger';
    				iconSymbol = 'fas fa-exclamation-triangle';
    			break;
    		}
    		$('#timeline-mobile div.timeline-item').append('<div class="row row-pagespeed pt-2 pb-2 jspeed-setting-row border-bottom">' +
    														  '<div class="col p-0 col-lg-8 text-left"><span class="' + iconSymbol + '" aria-hidden="true"></span> First Contentful Paint</div>' +
    														  '<div class="col p-0 col-lg-4 text-center"><span class="badge ' + fcpMobileLabel + '">' + fcpMobileFloat + ' s</span></div>' +
    													  	'</div>');
    
    		var siMobileLabel = '';
    		var siMobileFloat = parseFloat(data.siMobile);
    		switch(true) {
    			case (siMobileFloat >= 0 && siMobileFloat <= 3.4):
    				siMobileLabel = 'bg-success';
    				iconSymbol = 'fas fa-circle';
    			break;
    
    			case (siMobileFloat > 3.4 && siMobileFloat <= 5.8):
    				siMobileLabel = 'bg-warning';
    				iconSymbol = 'fas fa-square';
    			break;
    
    			case (siMobileFloat > 5.8):
    				siMobileLabel = 'bg-danger';
    				iconSymbol = 'fas fa-exclamation-triangle';
    			break;
    		}
    		$('#timeline-mobile div.timeline-item').append('<div class="row row-pagespeed pt-2 pb-2 jspeed-setting-row border-bottom">' +
    														  '<div class="col p-0 col-lg-8 text-left"><span class="' + iconSymbol + '" aria-hidden="true"></span> Speed Index</div>' +
    														  '<div class="col p-0 col-lg-4 text-center"><span class="badge ' + siMobileLabel + '">' + siMobileFloat + ' s</span></div>' +
    													  	'</div>');
    
    		var lcpMobileLabel = '';
    		var lcpMobileFloat = parseFloat(data.lcpMobile);
    		switch(true) {
    			case (lcpMobileFloat >= 0 && lcpMobileFloat <= 2.5):
    				lcpMobileLabel = 'bg-success';
    				iconSymbol = 'fas fa-circle';
    			break;
    
    			case (lcpMobileFloat > 2.5 && lcpMobileFloat <= 4):
    				lcpMobileLabel = 'bg-warning';
    				iconSymbol = 'fas fa-square';
    			break;
    
    			case (lcpMobileFloat > 4):
    				lcpMobileLabel = 'bg-danger';
    				iconSymbol = 'fas fa-exclamation-triangle';
    			break;
    		}
    		$('#timeline-mobile div.timeline-item').append('<div class="row row-pagespeed pt-2 pb-2 jspeed-setting-row border-bottom">' +
    														  '<div class="col p-0 col-lg-8 text-left"><span class="' + iconSymbol + '" aria-hidden="true"></span> Largest Contentful Paint</div>' +
    														  '<div class="col p-0 col-lg-4 text-center"><span class="badge ' + lcpMobileLabel + '">' + lcpMobileFloat + ' s</span></div>' +
    													  	'</div>');
    
    		var interactiveMobileLabel = '';
    		var interactiveMobileFloat = parseFloat(data.interactiveMobile);
    		switch(true) {
    			case (interactiveMobileFloat >= 0 && interactiveMobileFloat <= 3.8):
    				interactiveMobileLabel = 'bg-success';
    				iconSymbol = 'fas fa-circle';
    			break;
    
    			case (interactiveMobileFloat > 3.8 && interactiveMobileFloat <= 7.3):
    				interactiveMobileLabel = 'bg-warning';
    				iconSymbol = 'fas fa-square';
    			break;
    
    			case (interactiveMobileFloat > 7.3):
    				interactiveMobileLabel = 'bg-danger';
    				iconSymbol = 'fas fa-exclamation-triangle';
    			break;
    		}
    		$('#timeline-mobile div.timeline-item').append('<div class="row row-pagespeed pt-2 pb-2 jspeed-setting-row border-bottom">' +
    														  '<div class="col p-0 col-lg-8 text-left"><span class="' + iconSymbol + '" aria-hidden="true"></span> Time To Interactive</div>' +
    														  '<div class="col p-0 col-lg-4 text-center"><span class="badge ' + interactiveMobileLabel + '">' + data.interactiveMobile + ' s</span></div>' +
    													  	'</div>');
    
    		var tbtMobileLabel = '';
    		var tbtMobileInt = parseInt(data.tbtMobile);
    		switch(true) {
    			case (tbtMobileInt >= 0 && tbtMobileInt <= 200):
    				tbtMobileLabel = 'bg-success';
    				iconSymbol = 'fas fa-circle';
    			break;
    
    			case (tbtMobileInt > 200 && tbtMobileInt <= 600):
    				tbtMobileLabel = 'bg-warning';
    				iconSymbol = 'fas fa-square';
    			break;
    
    			case (tbtMobileInt > 600):
    				tbtMobileLabel = 'bg-danger';
    				iconSymbol = 'fas fa-exclamation-triangle';
    			break;
    		}
    		$('#timeline-mobile div.timeline-item').append('<div class="row row-pagespeed pt-2 pb-2 jspeed-setting-row border-bottom">' +
    														  '<div class="col p-0 col-lg-8 text-left"><span class="' + iconSymbol + '" aria-hidden="true"></span> Total Blocking Time</div>' +
    														  '<div class="col p-0 col-lg-4 text-center"><span class="badge ' + tbtMobileLabel + '">' + tbtMobileInt + ' ms</span></div>' +
    													  	'</div>');
    
    		var clsMobileLabel = '';
    		var clsMobileFloat = parseFloat(data.clsMobile);
    		switch(true) {
    			case (clsMobileFloat >= 0 && clsMobileFloat <= 0.1):
    				clsMobileLabel = 'bg-success';
    				iconSymbol = 'fas fa-circle';
    			break;
    
    			case (clsMobileFloat > 0.1 && clsMobileFloat <= 0.25):
    				clsMobileLabel = 'bg-warning';
    				iconSymbol = 'fas fa-square';
    			break;
    
    			case (clsMobileFloat > 0.25):
    				clsMobileLabel = 'bg-danger';
    				iconSymbol = 'fas fa-exclamation-triangle';
    			break;
    		}
    		$('#timeline-mobile div.timeline-item').append('<div class="row row-pagespeed pt-2 pb-2 jspeed-setting-row border-bottom">' +
    														  '<div class="col p-0 col-lg-8 text-left"><span class="' + iconSymbol + '" aria-hidden="true"></span> Cumulative Layout Shift</div>' +
    														  '<div class="col p-0 col-lg-4 text-center"><span class="badge ' + clsMobileLabel + '">' + clsMobileFloat + '</span></div>' +
    													  	'</div>');
    		$('#timeline-mobile div.timeline-item').append('<img class="jspeed-screenshot" alt="PageSpeed Screenshot" src="' + data.screenShotMobile + '"/>');
    	};
	
    	/**
    	 * Register user events for interface controls
    	 * 
    	 * @access private
    	 * @param Boolean initialize
    	 * @return Void
    	 */
    	var addListeners = function(initialize) {
    		$('#pagespeed-test').on('click', function(jqEvent){
    			$('div.pagespeed-test-url').removeClass('pagespeed-test-url-populated');
    			
    			// Replace the running status of the button
    			$('span.fas.fa-tachometer-alt', this).removeClass('fas fa-tachometer-alt').addClass('fas fa-cog running');
    			
    			$('div.timeline-item > *').remove();
				$('div.timeline-item').append('<div class="static-background"> <div class="background-masker header-top"></div><div class="background-masker header-left"></div><div class="background-masker header-right"></div><div class="background-masker subheader-left"></div><div class="background-masker subheader-bottom"></div><div class="background-masker content-top"></div><div class="background-masker content-first-end"></div><div class="background-masker content-second-end"></div><div class="background-masker content-third-end"></div><div class="background-masker content-forth-line"></div><div class="background-masker content-fifth-line"></div></div>');
    			$('div.timeline-item > div').addClass('animated-background');
    			
    			// Request JSON to com_ajax
    			var dataSourcePromise = $.Deferred(function(defer) {
    				$.ajax({
    					type : "POST",
    					url : my_ajax_object.ajax_url,
    					data : {
    						_ajax_nonce : my_ajax_object.nonce,
    						action : 'wpspeed_hook_pagespeed'
    					},
    					dataType : 'json'
    				}).done(function(data, textStatus, jqXHR) {
    					if(!data.success) {
    						// Error found
    						defer.reject('Error retrieving PageSpeed data', textStatus);
    						return false;
    					}
    					
    					if(typeof(data.pagespeedDesktop) === 'undefined' || typeof(data.pagespeedMobile) === 'undefined') {
    						// Error found
    						defer.reject('Missing PageSpeed data for this URL or invalid ApiKey', textStatus);
    						return false;
    					} 
    					
    					// Check response all went well
    					if(data.success) {
    						sessionAPIData = JSON.stringify(data);
        					sessionStorage.setItem('jspeed-google-pagespeed-data', sessionAPIData);
        					
    						defer.resolve(data);
    					}
    				}).fail(function(jqXHR, textStatus, errorThrown) {
    					// Error found
    					var genericStatus = textStatus[0].toUpperCase() + textStatus.slice(1);
    					defer.reject('-' + genericStatus + '- ' + errorThrown);
    				});
    			}).promise(); 
    			
    			dataSourcePromise.then(buildPageSpeedInterface, function(errorText, error) {
    				// Do stuff and exit
    				$('#pagespeed-test').after('<span class="badge bg-warning" id="defer-error-message"><span class="icon-warning"></span>' + errorText + '</span>');
    				setTimeout(function(){
    					$('#defer-error-message').remove();
    				}, 5000);
    			}).always(function(){
    				$('div.timeline-item > div').removeClass('animated-background');
    				
    				// Replace the running status of the button
        			$('#pagespeed-test span.fas.fa-cog.running').removeClass('fas fa-cog running').addClass('fas fa-tachometer-alt');
    			});
    		});
    		
    		$('#jform_params_pagespeedtest_domain_url').on('change', function(jqEvent){
    			$(this).addClass('jspeed-changed-domain');
    			$('#toolbar-apply,#toolbar-save').on('click', function(){
    				sessionStorage.removeItem('jspeed-google-pagespeed-data');
    			});
    		});
    		
    	};

		/**
		 * Function dummy constructor
		 * 
		 * @access private
		 * @param String
		 *            contextSelector
		 * @method <<IIFE>>
		 * @return Void
		 */
		(function __construct() {
			// Add UI events
			addListeners.call(this, true);
			
			sessionAPIData = sessionStorage.getItem('jspeed-google-pagespeed-data');
			if(sessionAPIData) {
				buildPageSpeedInterface(JSON.parse(sessionAPIData));
			}
		}).call(this);
	}
	
	//On DOM Ready
	$(function() {
		window.JSpeedPageSpeed = new PageSpeed();
	});
})(jQuery);