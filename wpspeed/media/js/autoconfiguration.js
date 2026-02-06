/**
 * Auto configuration class
 * 
 * @package JSPEED::plugins::system
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
//'use strict';
(function($) {
	var AutoConfiguration = function() {
		/**
    	 * Register user events for interface controls
    	 * 
    	 * @access private
    	 * @param Boolean initialize
    	 * @return Void
    	 */
    	var addListeners = function(initialize) {
    		var optimizationLevelSettings = {
    				1 : {
    					'wpspeed_settings_html_minify1' : {'action' : 'change', 'target' : 'wpspeed_settings_html_minify1'},
    					'wpspeed_settings_html_minify_level' : '0',
    					'wpspeed_settings_combine_files_enable1' : {'action' : 'change', 'target' : 'wpspeed_settings_combine_files_enable1'},
    					'wpspeed_settings_css1' : {'action' : 'change', 'target' : 'wpspeed_settings_css1'},
    					'wpspeed_settings_javascript0' : {'action' : 'change', 'target' : 'wpspeed_settings_javascript0'},
    					'wpspeed_settings_font_display_swap0' : {'action' : 'change', 'target' : 'wpspeed_settings_font_display_swap0'},
    					'wpspeed_settings_defer_combined_styles0' : {'action' : 'change', 'target' : 'wpspeed_settings_defer_combined_styles0'},
    					'wpspeed_settings_css_minify0' : {'action' : 'change', 'target' : 'wpspeed_settings_css_minify0'},
    					'wpspeed_settings_js_minify0' : {'action' : 'change', 'target' : 'wpspeed_settings_js_minify0'},
    					'wpspeed_settings_bottom_js0' : {'action' : 'change', 'target' : 'wpspeed_settings_bottom_js0'},
    					'wpspeed_settings_lazyload_enable0' : {'action' : 'change', 'target' : 'wpspeed_settings_lazyload_enable0'},
    					'wpspeed_settings_lightimgs_status0' : {'action' : 'change', 'target' : 'wpspeed_settings_lightimgs_status0'},
						'wpspeed_settings_img_attributes_enable0' : {'action' : 'change', 'target' : 'wpspeed_settings_img_attributes_enable0'}, 
    					'wpspeed_settings_convert_all_images_to_webp0' : {'action' : 'change', 'target' : 'wpspeed_settings_convert_all_images_to_webp0'},
    					'wpspeed_settings_convert_all_images_to_avif0' : {'action' : 'change', 'target' : 'wpspeed_settings_convert_all_images_to_avif0'},
    					'wpspeed_settings_adaptive_contents_enable0' : {'action' : 'change', 'target' : 'wpspeed_settings_adaptive_contents_enable0'},
    					'wpspeed_settings_adaptive_contents_remove_all_js0' : {'action' : 'change', 'target' : 'wpspeed_settings_adaptive_contents_remove_all_js0'},
    					'wpspeed_settings_adaptive_contents_remove_all_css0' : {'action' : 'change', 'target' : 'wpspeed_settings_adaptive_contents_remove_all_css0'},
						'wpspeed_settings_adaptive_contents_add_size_attributes0' : {'action' : 'change', 'target' : 'wpspeed_settings_adaptive_contents_add_size_attributes0'},
    					'wpspeed_settings_cache_enable0' : {'action' : 'change', 'target' : 'wpspeed_settings_cache_enable0'},
    					'wpspeed_settings_optimizeCssDelivery_enable0' : {'action' : 'change', 'target' : 'wpspeed_settings_optimizeCssDelivery_enable0'},
    					'wpspeed_settings_adaptive_contents_extract_css0' : {'action' : 'change', 'target' : 'wpspeed_settings_adaptive_contents_extract_css0'}
    				},
    				2 : {
    					'wpspeed_settings_html_minify1' : {'action' : 'change', 'target' : 'wpspeed_settings_html_minify1'},
    					'wpspeed_settings_html_minify_level' : '1',
    					'wpspeed_settings_combine_files_enable1' : {'action' : 'change', 'target' : 'wpspeed_settings_combine_files_enable1'},
    					'wpspeed_settings_css1' : {'action' : 'change', 'target' : 'wpspeed_settings_css1'},
    					'wpspeed_settings_javascript1' : {'action' : 'change', 'target' : 'wpspeed_settings_javascript1'},
    					'wpspeed_settings_font_display_swap1' : {'action' : 'change', 'target' : 'wpspeed_settings_font_display_swap1'},
    					'wpspeed_settings_defer_combined_styles0' : {'action' : 'change', 'target' : 'wpspeed_settings_defer_combined_styles0'},
    					'wpspeed_settings_css_minify0' : {'action' : 'change', 'target' : 'wpspeed_settings_css_minify0'},
    					'wpspeed_settings_js_minify0' : {'action' : 'change', 'target' : 'wpspeed_settings_js_minify0'},
    					'wpspeed_settings_bottom_js0' : {'action' : 'change', 'target' : 'wpspeed_settings_bottom_js0'},
    					'wpspeed_settings_lazyload_enable0' : {'action' : 'change', 'target' : 'wpspeed_settings_lazyload_enable0'},
    					'wpspeed_settings_lightimgs_status0' : {'action' : 'change', 'target' : 'wpspeed_settings_lightimgs_status0'},
						'wpspeed_settings_img_attributes_enable0' : {'action' : 'change', 'target' : 'wpspeed_settings_img_attributes_enable0'},
    					'wpspeed_settings_convert_all_images_to_webp0' : {'action' : 'change', 'target' : 'wpspeed_settings_convert_all_images_to_webp0'},
    					'wpspeed_settings_convert_all_images_to_avif0' : {'action' : 'change', 'target' : 'wpspeed_settings_convert_all_images_to_avif0'},
    					'wpspeed_settings_adaptive_contents_enable0' : {'action' : 'change', 'target' : 'wpspeed_settings_adaptive_contents_enable0'},
    					'wpspeed_settings_adaptive_contents_remove_all_js0' : {'action' : 'change', 'target' : 'wpspeed_settings_adaptive_contents_remove_all_js0'},
    					'wpspeed_settings_adaptive_contents_remove_all_css0' : {'action' : 'change', 'target' : 'wpspeed_settings_adaptive_contents_remove_all_css0'},
						'wpspeed_settings_adaptive_contents_add_size_attributes0' : {'action' : 'change', 'target' : 'wpspeed_settings_adaptive_contents_add_size_attributes0'},
    					'wpspeed_settings_cache_enable0' : {'action' : 'change', 'target' : 'wpspeed_settings_cache_enable0'},
    					'wpspeed_settings_optimizeCssDelivery_enable0' : {'action' : 'change', 'target' : 'wpspeed_settings_optimizeCssDelivery_enable0'},
    					'wpspeed_settings_adaptive_contents_extract_css0' : {'action' : 'change', 'target' : 'wpspeed_settings_adaptive_contents_extract_css0'}
    				},
    				3 : {
    					'wpspeed_settings_html_minify1' : {'action' : 'change', 'target' : 'wpspeed_settings_html_minify1'},
    					'wpspeed_settings_html_minify_level' : '1',
    					'wpspeed_settings_combine_files_enable1' : {'action' : 'change', 'target' : 'wpspeed_settings_combine_files_enable1'},
    					'wpspeed_settings_css1' : {'action' : 'change', 'target' : 'wpspeed_settings_css1'},
    					'wpspeed_settings_javascript1' : {'action' : 'change', 'target' : 'wpspeed_settings_javascript1'},
    					'wpspeed_settings_font_display_swap1' : {'action' : 'change', 'target' : 'wpspeed_settings_font_display_swap1'},
    					'wpspeed_settings_defer_combined_styles0' : {'action' : 'change', 'target' : 'wpspeed_settings_defer_combined_styles0'},
    					'wpspeed_settings_css_minify1' : {'action' : 'change', 'target' : 'wpspeed_settings_css_minify1'},
    					'wpspeed_settings_js_minify1' : {'action' : 'change', 'target' : 'wpspeed_settings_js_minify1'},
    					'wpspeed_settings_bottom_js0' : {'action' : 'change', 'target' : 'wpspeed_settings_bottom_js0'},
    					'wpspeed_settings_lazyload_enable1' : {'action' : 'change', 'target' : 'wpspeed_settings_lazyload_enable1'},
    					'wpspeed_settings_lightimgs_status0' : {'action' : 'change', 'target' : 'wpspeed_settings_lightimgs_status0'},
						'wpspeed_settings_img_attributes_enable0' : {'action' : 'change', 'target' : 'wpspeed_settings_img_attributes_enable0'},
    					'wpspeed_settings_convert_all_images_to_webp0' : {'action' : 'change', 'target' : 'wpspeed_settings_convert_all_images_to_webp0'},
    					'wpspeed_settings_convert_all_images_to_avif0' : {'action' : 'change', 'target' : 'wpspeed_settings_convert_all_images_to_avif0'},
    					'wpspeed_settings_adaptive_contents_enable0' : {'action' : 'change', 'target' : 'wpspeed_settings_adaptive_contents_enable0'},
    					'wpspeed_settings_adaptive_contents_remove_all_js0' : {'action' : 'change', 'target' : 'wpspeed_settings_adaptive_contents_remove_all_js0'},
    					'wpspeed_settings_adaptive_contents_remove_all_css0' : {'action' : 'change', 'target' : 'wpspeed_settings_adaptive_contents_remove_all_css0'},
						'wpspeed_settings_adaptive_contents_add_size_attributes0' : {'action' : 'change', 'target' : 'wpspeed_settings_adaptive_contents_add_size_attributes0'},
    					'wpspeed_settings_cache_enable0' : {'action' : 'change', 'target' : 'wpspeed_settings_cache_enable0'},
    					'wpspeed_settings_optimizeCssDelivery_enable0' : {'action' : 'change', 'target' : 'wpspeed_settings_optimizeCssDelivery_enable0'},
    					'wpspeed_settings_adaptive_contents_extract_css0' : {'action' : 'change', 'target' : 'wpspeed_settings_adaptive_contents_extract_css0'}
    				},
    				4 : {
    					'wpspeed_settings_html_minify1' : {'action' : 'change', 'target' : 'wpspeed_settings_html_minify1'},
    					'wpspeed_settings_html_minify_level' : '1',
    					'wpspeed_settings_combine_files_enable1' : {'action' : 'change', 'target' : 'wpspeed_settings_combine_files_enable1'},
    					'wpspeed_settings_css1' : {'action' : 'change', 'target' : 'wpspeed_settings_css1'},
    					'wpspeed_settings_javascript1' : {'action' : 'change', 'target' : 'wpspeed_settings_javascript1'},
    					'wpspeed_settings_font_display_swap1' : {'action' : 'change', 'target' : 'wpspeed_settings_font_display_swap1'},
    					'wpspeed_settings_defer_combined_styles1' : {'action' : 'change', 'target' : 'wpspeed_settings_defer_combined_styles1'},
    					'wpspeed_settings_css_minify1' : {'action' : 'change', 'target' : 'wpspeed_settings_css_minify1'},
    					'wpspeed_settings_js_minify1' : {'action' : 'change', 'target' : 'wpspeed_settings_js_minify1'},
    					'wpspeed_settings_bottom_js1' : {'action' : 'change', 'target' : 'wpspeed_settings_bottom_js1'},
    					'wpspeed_settings_lazyload_enable1' : {'action' : 'change', 'target' : 'wpspeed_settings_lazyload_enable1'},
    					'wpspeed_settings_lightimgs_status1' : {'action' : 'change', 'target' : 'wpspeed_settings_lightimgs_status1'},
						'wpspeed_settings_img_attributes_enable0' : {'action' : 'change', 'target' : 'wpspeed_settings_img_attributes_enable0'},
    					'wpspeed_settings_convert_all_images_to_webp1' : {'action' : 'change', 'target' : 'wpspeed_settings_convert_all_images_to_webp1'},
    					'wpspeed_settings_convert_all_images_to_avif0' : {'action' : 'change', 'target' : 'wpspeed_settings_convert_all_images_to_avif0'},
    					'wpspeed_settings_adaptive_contents_enable1' : {'action' : 'change', 'target' : 'wpspeed_settings_adaptive_contents_enable1'},
    					'wpspeed_settings_adaptive_contents_remove_all_js1' : {'action' : 'change', 'target' : 'wpspeed_settings_adaptive_contents_remove_all_js1'},
    					'wpspeed_settings_adaptive_contents_remove_all_css0' : {'action' : 'change', 'target' : 'wpspeed_settings_adaptive_contents_remove_all_css0'},
						'wpspeed_settings_adaptive_contents_add_size_attributes0' : {'action' : 'change', 'target' : 'wpspeed_settings_adaptive_contents_add_size_attributes0'},
    					'wpspeed_settings_cache_enable0' : {'action' : 'change', 'target' : 'wpspeed_settings_cache_enable0'},
    					'wpspeed_settings_optimizeCssDelivery_enable0' : {'action' : 'change', 'target' : 'wpspeed_settings_optimizeCssDelivery_enable0'},
    					'wpspeed_settings_adaptive_contents_extract_css0' : {'action' : 'change', 'target' : 'wpspeed_settings_adaptive_contents_extract_css0'}
    				},
    				5 : {
    					'wpspeed_settings_html_minify1' : {'action' : 'change', 'target' : 'wpspeed_settings_html_minify1'},
    					'wpspeed_settings_html_minify_level' : '2',
    					'wpspeed_settings_combine_files_enable1' : {'action' : 'change', 'target' : 'wpspeed_settings_combine_files_enable1'},
    					'wpspeed_settings_css1' : {'action' : 'change', 'target' : 'wpspeed_settings_css1'},
    					'wpspeed_settings_javascript1' : {'action' : 'change', 'target' : 'wpspeed_settings_javascript1'},
    					'wpspeed_settings_font_display_swap1' : {'action' : 'change', 'target' : 'wpspeed_settings_font_display_swap1'},
    					'wpspeed_settings_defer_combined_styles1' : {'action' : 'change', 'target' : 'wpspeed_settings_defer_combined_styles1'},
    					'wpspeed_settings_css_minify1' : {'action' : 'change', 'target' : 'wpspeed_settings_css_minify1'},
    					'wpspeed_settings_js_minify1' : {'action' : 'change', 'target' : 'wpspeed_settings_js_minify1'},
    					'wpspeed_settings_bottom_js1' : {'action' : 'change', 'target' : 'wpspeed_settings_bottom_js1'},
    					'wpspeed_settings_lazyload_enable1' : {'action' : 'change', 'target' : 'wpspeed_settings_lazyload_enable1'},
    					'wpspeed_settings_lightimgs_status1' : {'action' : 'change', 'target' : 'wpspeed_settings_lightimgs_status1'},
						'wpspeed_settings_img_attributes_enable1' : {'action' : 'change', 'target' : 'wpspeed_settings_img_attributes_enable1'},
    					'wpspeed_settings_convert_all_images_to_webp1' : {'action' : 'change', 'target' : 'wpspeed_settings_convert_all_images_to_webp1'},
    					'wpspeed_settings_convert_all_images_to_avif0' : {'action' : 'change', 'target' : 'wpspeed_settings_convert_all_images_to_avif0'},
    					'wpspeed_settings_adaptive_contents_enable1' : {'action' : 'change', 'target' : 'wpspeed_settings_adaptive_contents_enable1'},
    					'wpspeed_settings_adaptive_contents_remove_all_js1' : {'action' : 'change', 'target' : 'wpspeed_settings_adaptive_contents_remove_all_js1'},
    					'wpspeed_settings_adaptive_contents_remove_all_css1' : {'action' : 'change', 'target' : 'wpspeed_settings_adaptive_contents_remove_all_css1'},
						'wpspeed_settings_adaptive_contents_add_size_attributes1' : {'action' : 'change', 'target' : 'wpspeed_settings_adaptive_contents_add_size_attributes1'},
    					'wpspeed_settings_cache_enable1' : {'action' : 'change', 'target' : 'wpspeed_settings_cache_enable1'},
    					'wpspeed_settings_optimizeCssDelivery_enable1' : {'action' : 'change', 'target' : 'wpspeed_settings_optimizeCssDelivery_enable1'},
    					'wpspeed_settings_adaptive_contents_extract_css1' : {'action' : 'change', 'target' : 'wpspeed_settings_adaptive_contents_extract_css1'}
    				}
    		};
    		var optimizationIconsMapping = {
    				'wpspeed_settings_html_minify1' : 'fas fa-compress',
    				'wpspeed_settings_html_minify_level' : 'fas fa-compress',
    				'wpspeed_settings_combine_files_enable0' : 'fas fa-object-group',
    				'wpspeed_settings_combine_files_enable1' : 'fas fa-object-group',
    				'wpspeed_settings_css0' : 'fas fa-object-group',
    				'wpspeed_settings_css1' : 'fas fa-object-group',
    				'wpspeed_settings_javascript0' : 'fas fa-object-group',
    				'wpspeed_settings_javascript1' : 'fas fa-object-group',
    				'wpspeed_settings_font_display_swap0' : 'fas fa-tachometer-alt',
    				'wpspeed_settings_font_display_swap1' : 'fas fa-tachometer-alt',
    				'wpspeed_settings_defer_combined_styles0' : 'fas fa-tachometer-alt',
    				'wpspeed_settings_defer_combined_styles1' : 'fas fa-tachometer-alt',
    				'wpspeed_settings_css_minify0' : 'fas fa-compress',
    				'wpspeed_settings_css_minify1' : 'fas fa-compress',
    				'wpspeed_settings_js_minify0' : 'fas fa-compress',
    				'wpspeed_settings_js_minify1' : 'fas fa-compress',
    				'wpspeed_settings_bottom_js0' : 'fas fa-download',
    				'wpspeed_settings_bottom_js1' : 'fas fa-download',
    				'wpspeed_settings_lazyload_enable0' : 'fas fa-images',
    				'wpspeed_settings_lazyload_enable1' : 'fas fa-images',
    				'wpspeed_settings_lightimgs_status0' : 'fas fa-image',
    				'wpspeed_settings_lightimgs_status1' : 'fas fa-image',
					'wpspeed_settings_img_attributes_enable0' : 'fas fa-compress',
					'wpspeed_settings_img_attributes_enable1' : 'fas fa-compress',
    				'wpspeed_settings_convert_all_images_to_webp0' : 'fas fa-images',
    				'wpspeed_settings_convert_all_images_to_webp1' : 'fas fa-images',
    				'wpspeed_settings_convert_all_images_to_avif0' : 'fas fa-images',
    				'wpspeed_settings_convert_all_images_to_avif1' : 'fas fa-images',
    				'wpspeed_settings_adaptive_contents_enable0' : 'fas fa-random',
    				'wpspeed_settings_adaptive_contents_enable1' : 'fas fa-random',
    				'wpspeed_settings_adaptive_contents_remove_all_js0' : 'fas fa-random',
    				'wpspeed_settings_adaptive_contents_remove_all_js1' : 'fas fa-random',
    				'wpspeed_settings_adaptive_contents_remove_all_css0' : 'fas fa-random',
    				'wpspeed_settings_adaptive_contents_remove_all_css1' : 'fas fa-random',
    				'wpspeed_settings_cache_enable0' : 'fas fa-database',
					'wpspeed_settings_cache_enable1' : 'fas fa-database',
					'wpspeed_settings_adaptive_contents_add_size_attributes0' : 'fas fa-compress',
					'wpspeed_settings_adaptive_contents_add_size_attributes1' : 'fas fa-compress',
					'wpspeed_settings_optimizeCssDelivery_enable0' : 'fas fa-object-group',
					'wpspeed_settings_optimizeCssDelivery_enable1' : 'fas fa-object-group',
					'wpspeed_settings_adaptive_contents_extract_css0' : 'fas fa-object-group',
					'wpspeed_settings_adaptive_contents_extract_css1' : 'fas fa-object-group'
    		};
    		var optimizationLevel = parseInt($('#wpspeed_settings_rangeSlider').val());
    		$('#wpspeed_settings_rangeSlider').on('change', function(jqEvent, doNotRefreshSettings){
    			optimizationLevel = $(this).val();
    			$('span.innerlabel').removeClass('badge bg-primary');
    			$('span.innerlabel[data-label=' + optimizationLevel + ']').addClass('badge bg-primary');
    			$('#optimizationslist').empty();
    			$.each(optimizationLevelSettings[optimizationLevel], function(control, controlValue){
    				if(typeof(controlValue) !== "object") {
    					var textOptimizationContext = $('#' + control).parents('tr');
    					if(!doNotRefreshSettings) {
    						$('#' + control).val(controlValue);
    					}
    				} else {
    					var textOptimizationContext = $('label[for=' + controlValue.target + ']').parents('tr');
    					if(!doNotRefreshSettings) {
							var $input = $('#' + controlValue.target);
							if ($input.length) {
							    $input.prop('checked', true).trigger(controlValue.action);
							}
    					}
    				}
    				
    				var targetControl = $('*[id="' + control + '"]');
    				var labelClass = 'bg-primary';
    				var labelIcon = '<span class="fas fa-info-circle"></span>';
    				if(targetControl.get(0).nodeName.toLowerCase() == 'select') {
    					var optionValue = targetControl.val();
    					var controlCalculatedValue = $('option[value=' + optionValue + ']', targetControl).text();
    				} else {
    					var controlCalculatedValue = parseInt(targetControl.val()) ? 'Enabled' : 'Disabled';
    					labelClass = parseInt(targetControl.val()) ? 'bg-success' : 'bg-secondary';
    					labelIcon = parseInt(targetControl.val()) ? '<span class="fas fa-check"></span>' : '<span class="fas fa-times-circle"></span>';
    				} 
    				var textOptimizationLabel = $('th div.title', textOptimizationContext).clone()
                                                                    				      .children()
                                                                    				      .remove()
                                                                    				      .end()
                                                                    				      .text();
    				$('#optimizationslist').append(	'<div class="row pt-2 pb-2 jspeed-setting-row border-bottom">' +
    						'<div class="col col-lg-6"><span class="' + optimizationIconsMapping[control] + '" aria-hidden="true"></span>' + textOptimizationLabel + '</div>' +
    						'<div class="col col-lg-5"><span class="badge ' + labelClass + '">' + labelIcon + controlCalculatedValue + '</span></div>' +
    				'</div>');
    			});
    			
    			if(optimizationLevel == 0) {
    				$('#optimizationslist').removeClass('populated');
    			} else {
    				$('#optimizationslist').addClass('populated');
    			}
    		});
    		if(optimizationLevel > 0) {
    			$('#wpspeed_settings_rangeSlider').trigger('change', [true]);
    			$('#optimizationslist').addClass('populated');
    		} else {
    			$('span.innerlabel[data-label=0]').addClass('badge bg-primary');
    		}

			// Auto configuration slider management
			const slider = document.getElementById("wpspeed_settings_rangeSlider");
			function updateProgress() {
			  const val = slider.value * 20;
			  document.documentElement.style.setProperty("--range-progress", val + "%");
			}
			updateProgress();
			slider.addEventListener("input", updateProgress);
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
			
			$('#wpspeed_settings_rangeSlider').removeClass('form-range');
			
		}).call(this);
	}
	
	//On DOM Ready
	$(function() {
		window.JSpeedAutoConfiguration = new AutoConfiguration();
	});
})(jQuery);