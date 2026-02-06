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

class TabContent
{
	public static function start()
	{
		return <<<HTML
<div class="tab-content">
	<div style="display:none">
		<fieldset>
			<div>
HTML;

	}

	public static function addTab( $id, $active = false )
	{
		$active = $active ? ' active' : '';

		return <<<HTML
			</div>
		</fieldset>
	</div>		
	<div class="tab-pane{$active}" id="{$id}">
		<fieldset style="display: none;">
			<div>
HTML;

	}

	public static function addSection( $header = '', $description = '', $class = '' )
	{
		return <<<HTML
			</div>
		</fieldset>
		<fieldset class="wpspeed-group">
			<div class="wpspeed-group-header {$class}">
				<h4 class="setting-header">$header</h4>
				<p class="setting-header-description">{$description}</p>
			</div>
			<div>
HTML;
	}

	public static function end()
	{
		return <<<HTML
			</div>
		</fieldset>
	</div>
</div>
HTML;
	}
}