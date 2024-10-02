<?php

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

namespace WPSpeed\Admin\Settings;

use WPSpeed\Core\Admin\MultiSelectItems;

class Html
{
	/**
	 * @param $key
	 * @param $settingName
	 * @param $defaultValue
	 * @param ...$aArgs
	 *
	 * @return false|mixed|string
	 */
	public static function _( $key, $settingName, $defaultValue, ...$aArgs )
	{
		list( $function, $proOnly ) = static::extract( $key );

		$aSavedSettings = get_option( 'wpspeed_settings' );

		if ( ! isset( $aSavedSettings[$settingName] ) )
		{
			$activeValue = $defaultValue;
		}
		else
		{
			$activeValue = $aSavedSettings[$settingName];
		}

		$callable = [ __CLASS__, $function ];

		//prepend $settingName, $activeValue to arguments
		array_unshift( $aArgs, $settingName, $activeValue );

		if($key == 'multiselect') {
			array_push( $aArgs, $defaultValue);
		}

		return call_user_func_array( $callable, $aArgs );
	}

	/**
	 * @param $key
	 *
	 * @return array
	 */
	protected static function extract( $key )
	{
		$parts = explode( '.', $key );

		$function = $parts[0];
		$proOnly  = isset( $parts[1] ) && $parts[1] === 'pro';

		return [ $function, $proOnly ];
	}

	/**
	 * @param          $title
	 * @param          $description
	 * @param   false  $new
	 *
	 * @return string
	 */
	public static function description( $title, $description, $new = false )
	{
		$text = '<div class="title">' . $title;

		if ( $description )
		{
			$text .= '<div class="description"><span class="fa fa-info-circle"></span><div><p>' . $description . '</p></div></div>';
		}

		if ( $new )
		{
			$text .= ' <span class="badge badge-danger">New!</span>';
		}

		$text .= '</div>';

		return $text;

	}

	/**
	 * @param           $settingName
	 * @param           $activeValue
	 * @param   string  $class
	 *
	 * @return string
	 */
	public static function radio( $settingName, $activeValue, $class = '' )
	{
		$checked = 'checked="checked"';
		$no      = '';
		$yes     = '';

		if ( $activeValue == '1' )
		{
			$yes = $checked;
		}
		else
		{
			$no = $checked;
		}

		$noText  = __( 'No', 'wpspeed' );
		$yesText = __( 'Yes', 'wpspeed' );

		$radioHtml = <<<HTML
<fieldset id="wpspeed_settings_{$settingName}" class="btn-group {$class}" role="group" aria-label="Radio toggle button group">
	<input type="radio" id="wpspeed_settings_{$settingName}0" name="wpspeed_settings[{$settingName}]" class="btn-check" value="0" {$no} >
	<label for="wpspeed_settings_{$settingName}0" class="btn btn-outline-secondary">{$noText}</label>
	<input type="radio" id="wpspeed_settings_{$settingName}1" name="wpspeed_settings[{$settingName}]" class="btn-check" value="1" {$yes} >
	<label for="wpspeed_settings_{$settingName}1" class="btn btn-outline-secondary">{$yesText}</label>
</fieldset>
		
HTML;

		return $radioHtml;
	}

	/**
	 * @param           $settingName
	 * @param           $activeValue
	 * @param           $aOptions
	 * @param   string  $class
	 *
	 * @return string
	 */
	public static function select( $settingName, $activeValue, $aOptions, $class = '', $multiple = false )
	{
		$optionsHtml = '';
		$multipleAttribute = $multiple ? 'multiple' : '';
		$multipleArray = $multiple ? '[]' : '';

		foreach ( $aOptions as $key => $value )
		{
			if(is_array($activeValue)) {
				$selected = in_array($key, $activeValue) ? ' selected="selected"' : '';
			} else {
				$selected = $activeValue == $key ? ' selected="selected"' : '';
			}

			$optionsHtml .= <<<HTML
<option value="{$key}"{$selected}>$value</option>
HTML;
		}

		$selectHtml = <<<HTML
<select id="wpspeed_settings_{$settingName}" name="wpspeed_settings[{$settingName}]{$multipleArray}" class="{$class}" {$multipleAttribute}>
	{$optionsHtml}
</select>
HTML;

		return $selectHtml;
	}

	/**
	 * @param           $settingName
	 * @param           $aActiveValues
	 * @param           $aOptions
	 * @param   string  $defaultValues
	 *
	 * @return string
	 */
	public static function multiselect( $settingName, $aActiveValues, $type, $group, $defaultValues = array() )
	{
		$optionsHtml = '';

		foreach ( $aActiveValues as $value )
		{
			$option = MultiSelectItems::{'prepare' . ucfirst( $group ) . 'Values'}( $value );

			$optionsHtml .= <<<HTML
<option value="{$value}" selected>$option</option>
HTML;
		}
		
		// Add all other default options if any as non selected
		if(count($defaultValues)) {
			foreach ($defaultValues as $default) {
				if(in_array($default, $aActiveValues)) {
					continue;
				}
				
				$defaultOption = MultiSelectItems::{'prepare' . ucfirst( $group ) . 'Values'}( $default );
				
				$optionsHtml .= <<<HTML
<option value="{$default}">$defaultOption</option>
HTML;
			}
		}

		$imgSrc      = WPSPEED_URL . 'media/core/images/loading.gif';

		$multiSelectHtml = <<<HTML
<select id="wpspeed_settings_{$settingName}" name="wpspeed_settings[{$settingName}][]" class="wpspeed-multiselect select2-ctrl" multiple="multiple" size="5" data-wpspeed_type="{$type}" data-wpspeed_group="{$group}" data-wpspeed_param="{$settingName}" >
	{$optionsHtml}
</select>
<img id="img-{$settingName}" class="wpspeed-multiselect-loading-image" src="{$imgSrc}" />
HTML;

		return $multiSelectHtml;
	}

	/**
	 * @param           $settingName
	 * @param           $activeValue
	 * @param   string  $size
	 * @param   string  $class
	 *
	 * @return string
	 */
	public static function text( $settingName, $activeValue,  $size = '30', $class = '' )
	{
		$textInputHtml = <<<HTML
<input type="text" id="wpspeed_settings_{$settingName}" name="wpspeed_settings[{$settingName}]" value="{$activeValue}" size="{$size}" class="{$class}">
HTML;

		return $textInputHtml;
	}
	
	/**
	 * @param           $settingName
	 * @param           $activeValue
	 * @param   string  $size
	 * @param   string  $class
	 *
	 * @return string
	 */
	public static function textarea( $settingName, $activeValue, $rows = '10', $cols = '50', $class = '' )
	{
		$textInputHtml = <<<HTML
<textarea id="wpspeed_settings_{$settingName}" name="wpspeed_settings[{$settingName}]" rows="{$rows}" cols="{$cols}" class="{$class}">{$activeValue}</textarea>
HTML;
		
		return $textInputHtml;
	}

	/**
	 * @param           $settingName
	 * @param           $activeValue
	 * @param   string  $class
	 *
	 * @return string
	 */
	public static function checkbox( $settingName, $activeValue, $class = '' )
	{
		$checked = $activeValue == '1' ? 'checked="checked"' : '';
		$offText = __( 'No', 'wpspeed' );
		$onText  = __( 'Yes', 'wpspeed' );

		$checkBoxHtml = <<<HTML
<input type="checkbox" id="wpspeed_settings_{$settingName}" class="{$class}" name="wpspeed_settings[$settingName]" data-toggle="toggle" data-onstyle="success" data-offstyle="danger" data-on="{$onText}" data-off="{$offText}" value="1" {$checked}>
HTML;

		return $checkBoxHtml;
	}

	/**
	 * @param           $settingName
	 * @param           $aActiveValues
	 * @param           $aOptions
	 * @param   string  $class
	 *
	 * @return string
	 */
	public static function checkboxes( $settingName, $aActiveValues, $aOptions, $class = '' )
	{
		$optionsHtml = '';
		$i           = '0';

		foreach ( $aOptions as $key => $value )
		{
			$checked = '';

			if ( is_array( $aActiveValues ) && in_array( $key, $aActiveValues ) )
			{
				$checked = 'checked';
			}

			$optionsHtml .= <<<HTML
<li>
	<input type="checkbox" id="wpspeed_settings_{$settingName}{$i}" name="wpspeed_settings[$settingName][]" value="{$key}" $checked>
	<label for="wpspeed_settings_{$settingName}{$i}">{$value}</label>
</li>
HTML;
			$i ++;
		}

		$checkboxesHtml = <<<HTML
<fieldset id="wpspeed_settings_{$settingName}" class="{$class}">
	<ul>
		{$optionsHtml}
	</ul>
</fieldset>
HTML;

		return $checkboxesHtml;
	}
	
	/**
	 * @param           $settingName
	 * @param           $aActiveValues
	 *
	 * @return string
	 */
	public static function rangeSlider( $settingName, $aActiveValues )
	{
		wp_register_script ( 'wpspeed-autoconfiguration-js', WPSPEED_URL . 'media/js/autoconfiguration.js', [
				'jquery'
		], WPSPEED_VERSION, true );
		wp_enqueue_script ( 'wpspeed-autoconfiguration-js' );
		
		wp_register_style( 'wpspeed-autoconfiguration-css', WPSPEED_URL . 'media/css/autoconfiguration.css', [], WPSPEED_VERSION );
		wp_enqueue_style( 'wpspeed-autoconfiguration-css' );
		
		$rangeSlider = '<div id="rangecontainer">';
		$rangeSlider .= '<input type="range" name="wpspeed_settings[' . $settingName . ']" id="wpspeed_settings_' . $settingName . '" value="' . $aActiveValues . '" class="valid form-control-success"  max="5" step="1" aria-invalid="false">';
		$rangeSlider .= '<div id="rangelabels">' .
				'<span data-container-label="0"><span class="innerlabel" data-label="0">' . __ ( 'Custom', 'wpspeed' ) . '</span></span>' .
				'<span data-container-label="1"><span class="innerlabel" data-label="1">' . __ ( 'Minimum', 'wpspeed' ) . '</span></span>' .
				'<span data-container-label="2"><span class="innerlabel" data-label="2">' . __ ( 'Standard', 'wpspeed' ) . '</span></span>' .
				'<span data-container-label="3"><span class="innerlabel" data-label="3">' . __ ( 'Average', 'wpspeed' ) . '</span></span>' .
				'<span data-container-label="4"><span class="innerlabel" data-label="4">' . __ ( 'Optimal', 'wpspeed' ) . '</span></span>' .
				'<span data-container-label="5"><span class="innerlabel" data-label="5">' . __ ( 'Maximum', 'wpspeed' ) . '</span></span>' .
				'</div>';
		$rangeSlider .= '</div>';
		$rangeSlider .= '<div id="optimizationslist" class="mt-2 border"></div>';
		
		return $rangeSlider;
	}
	
	/**
	 * @param           $settingName
	 * @param           $aActiveValues
	 *
	 * @return string
	 */
	public static function pageSpeed ( $settingName ) {
		
		wp_register_style( 'wpspeed-pagespeed-css', WPSPEED_URL . 'media/css/pagespeed.css', [], WPSPEED_VERSION );
		wp_enqueue_style( 'wpspeed-pagespeed-css' );
		
		wp_register_script ( 'wpspeed-progressring-js', WPSPEED_URL . 'media/js/progress-ring.js' );
		wp_enqueue_script ( 'wpspeed-progressring-js' );
		
		wp_register_script ( 'wpspeed-pagespeed-js', WPSPEED_URL .'media/js/pagespeed.js' );
		wp_enqueue_script ( 'wpspeed-pagespeed-js' );
		
		wp_localize_script ('wpspeed-pagespeed-js', 'my_ajax_object', array(
				'ajax_url'=>admin_url('admin-ajax.php'),
				'nonce'=>wp_create_nonce('wpspeed')
		));
		
		$htmlControl = <<<HTMLCONTROL
			<a class="btn btn-sm btn-primary" id="pagespeed-test"><span class="fas fa-tachometer-alt"></span> Test Google PageSpeed Insights</a>
			<div class="pagespeed-test-url badge bg-light"></div>
			<div class="timeline-container">
				<div class="timeline-wrapper" id="timeline-mobile">
				    <div class="timeline-item">
				        <div class="static-background">
				            <div class="background-masker header-top"></div>
				            <div class="background-masker header-left"></div>
				            <div class="background-masker header-right"></div>
				            <div class="background-masker subheader-left"></div>
				            <div class="background-masker subheader-bottom"></div>
				            <div class="background-masker content-top"></div>
				            <div class="background-masker content-first-end"></div>
				            <div class="background-masker content-second-end"></div>
				            <div class="background-masker content-third-end"></div>
							<div class="background-masker content-forth-line"></div>
							<div class="background-masker content-fifth-line"></div>
				        </div>
				    </div>
				</div>
				<div class="timeline-wrapper" id="timeline-desktop">
				    <div class="timeline-item">
				        <div class="static-background">
				            <div class="background-masker header-top"></div>
				            <div class="background-masker header-left"></div>
				            <div class="background-masker header-right"></div>
				            <div class="background-masker subheader-left"></div>
				            <div class="background-masker subheader-bottom"></div>
				            <div class="background-masker content-top"></div>
				            <div class="background-masker content-first-end"></div>
				            <div class="background-masker content-second-end"></div>
				            <div class="background-masker content-third-end"></div>
							<div class="background-masker content-forth-line"></div>
							<div class="background-masker content-fifth-line"></div>
				        </div>
				    </div>
				</div>
			</div>
HTMLCONTROL;
		
		return $htmlControl;
	}
}