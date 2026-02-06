/**
 * WPSpeed - Performs several front-end optimizations for fast downloads
 *
 * @package   WPSpeed
 * @author    JExtensions Store <info@storejextensions.org>
 * @copyright Copyright (c) 2022 JExtensions Store / WPSpeed
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

var adminUtilities = (function($) {

	let wpspeed_ajax_url_optimizeimages = ajaxurl + '?action=optimizeimages';
	let wpspeed_ajax_url_multiselect = ajaxurl + '?action=multiselect';


	let configure_url = "options-general.php?page=wpspeed&view=configure";

	var submitForm = function() {
		document.getElementById('wpspeed-settings-form').submit();
	}

	/**
	 * Toggle fields visibility based on SimpleHtmlDom switcher
	 */
	var toggleSimpleHtmlDomFields = function() {
		var useSimpleHtmlDom = $('input[name="wpspeed_settings[use_simplehtmldom]"]:checked').val();

		if (useSimpleHtmlDom === "1") {
			// Show Decode text entities
			$('#wpspeed_settings_img_processing_simplehtmldom_entity_decode').closest('tr').show();

			// Hide standard decode/purify fields
			$('#wpspeed_settings_img_processing_entity_decode').closest('tr').hide();
			$('#wpspeed_settings_img_processing_utf8_entity_decode').closest('tr').hide();
			$('#wpspeed_settings_purify_string').closest('tr').hide();
			$('#wpspeed_settings_purify_string_replacement').closest('tr').hide();
		} else {
			// Hide Decode text entities
			$('#wpspeed_settings_img_processing_simplehtmldom_entity_decode').closest('tr').hide();

			// Show standard decode/purify fields
			$('#wpspeed_settings_img_processing_entity_decode').closest('tr').show();
			$('#wpspeed_settings_img_processing_utf8_entity_decode').closest('tr').show();
			$('#wpspeed_settings_purify_string').closest('tr').show();
			$('#wpspeed_settings_purify_string_replacement').closest('tr').show();
		}
	};

	// Init on page load
	$(document).ready(function() {
		toggleSimpleHtmlDomFields();

		// Listen for changes
		$(document).on('change', 'input[name="wpspeed_settings[use_simplehtmldom]"]', function() {
			toggleSimpleHtmlDomFields();
		});
	});

	function initWPSwitch($fs) {
		if (!$fs.hasClass('wpswitch')) $fs.addClass('wpswitch').attr('role', 'switch').attr('tabindex', '0');

		let $tr  = $fs.closest('tr');
		let $th  = $tr.find('th');
		let $td  = $tr.find('td');
			
		let $led = $fs.closest('tr').find('.circle-check');
	    if ($led.length === 0) {
	      $led = $('<div class="circle-check"></div>');
	      $fs.closest('tr').find('th .title').before($led);
	    }
		
		function isOn() {
			return $fs.find('input[type=radio][value="1"]').prop('checked');
		}

		function render() {
			const on = isOn();
			$fs.toggleClass('on', on).toggleClass('off', !on).attr('aria-checked', on ? 'true' : 'false');
			$led.toggleClass('active', on);
		}

		function setState(on) {
			$fs.find('input[type=radio][value="1"]').prop('checked', on);
			$fs.find('input[type=radio][value="0"]').prop('checked', !on);
			$fs.find('input[type=radio]:checked').trigger('change');
			render();
		}

		function toggle() {
			setState(!isOn());
		}

		render();

		$fs.find('input[type=radio]').off('change.wpswitch').on('change.wpswitch', render);

		$fs.off('click.wpswitch').on('click.wpswitch', function(e) {
			if ($(e.target).is('label')) {
				e.preventDefault();
			}
			if ($(e.target).is('input')) return;

			toggle();
		});

		$fs.off('keydown.wpswitch').on('keydown.wpswitch', function(e) {
			if (e.which === 13 || e.which === 32) { e.preventDefault(); toggle(); }
		});
	}

	$(function() {
		$('.form-table fieldset.btn-group').each(function() {
			const $fs = $(this);
			const radios = $fs.find('input[type=radio].btn-check');
			if (radios.length === 2 &&
				radios.filter('[value="0"]').length === 1 &&
				radios.filter('[value="1"]').length === 1) {
				initWPSwitch($fs);
			}
		});
		
		// Move all .settingdesc out of TH into TD (for all params)
		$('.form-table tr').each(function () {
			const $tr = $(this);
			const $th = $tr.find('th');
			const $td = $tr.find('td');
			const $desc = $th.find('.settingdesc');
			if ($desc.length) {
				$td.append($desc); // move description under the input field
			}
		});
		
		const stats = document.querySelector(".wpspeed-stats");
		const generalRow = document.querySelector("div.wpspeed-group-header.general + div table.form-table tr");

		if (stats && generalRow) {
			const newCell = document.createElement("th");
			newCell.classList.add('stats-th');
		    newCell.appendChild(stats);
		    generalRow.prepend(newCell);
		}
		  
		function getAdaptiveMax(value) {
		  if (value <= 10) return 10;
		  if (value <= 100) return 100;
		  if (value <= 500) return 500;
		  if (value <= 1000) return 1000;
		  if (value <= 5000) return 5000;
		  if (value <= 10000) return 10000;
		  return Math.pow(10, Math.ceil(Math.log10(value))); // arrotonda alla potenza di 10 più vicina
		}
		
		document.querySelectorAll(".circle").forEach(circle => {
		    const value = parseFloat(circle.dataset.value) || 0;

		    const max = getAdaptiveMax(value); // calcolo max dinamico
		    const r = 60;
		    const circumference = 2 * Math.PI * r;

		    // crea SVG
		    const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
		    svg.setAttribute("viewBox", "0 0 140 140");

		    const defs = document.createElementNS("http://www.w3.org/2000/svg", "defs");
		    const gradient = document.createElementNS("http://www.w3.org/2000/svg", "linearGradient");
		    gradient.setAttribute("id", "grad1");
		    gradient.setAttribute("x1", "0%");
		    gradient.setAttribute("y1", "0%");
		    gradient.setAttribute("x2", "100%");
		    gradient.setAttribute("y2", "0%");
		    const stop1 = document.createElementNS("http://www.w3.org/2000/svg", "stop");
		    stop1.setAttribute("offset", "0%");
		    stop1.setAttribute("stop-color", "#3b82f6");
		    const stop2 = document.createElementNS("http://www.w3.org/2000/svg", "stop");
		    stop2.setAttribute("offset", "100%");
		    stop2.setAttribute("stop-color", "#06b6d4");
		    gradient.appendChild(stop1);
		    gradient.appendChild(stop2);
		    defs.appendChild(gradient);
		    svg.appendChild(defs);

		    const bg = document.createElementNS("http://www.w3.org/2000/svg", "circle");
		    bg.setAttribute("cx", "70");
		    bg.setAttribute("cy", "70");
		    bg.setAttribute("r", r);
		    bg.setAttribute("class", "bg");

		    const progress = document.createElementNS("http://www.w3.org/2000/svg", "circle");
		    progress.setAttribute("cx", "70");
		    progress.setAttribute("cy", "70");
		    progress.setAttribute("r", r);
		    progress.setAttribute("class", "progress");
		    progress.setAttribute("stroke-dasharray", circumference);
		    progress.setAttribute("stroke-dashoffset", circumference);

		    svg.appendChild(bg);
		    svg.appendChild(progress);
		    circle.insertBefore(svg, circle.querySelector(".circle-inner"));

		    // animazione
		    setTimeout(() => {
		      const ratio = Math.min(1, value / max); // valore rapportato al max dinamico
		      const offset = circumference * (1 - ratio);
		      progress.style.transition = "stroke-dashoffset 1.5s ease";
		      progress.setAttribute("stroke-dashoffset", offset);
		    }, 300);
		  });
	});

	return {
		//properties
		wpspeed_ajax_url_optimizeimages: wpspeed_ajax_url_optimizeimages,
		wpspeed_ajax_url_multiselect: wpspeed_ajax_url_multiselect,
		//methods
		submitForm: submitForm
	}

})(jQuery);