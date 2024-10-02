<?php /* E:\vhosts\wordpressdev\wp-content\plugins\wpspeed\src\Admin\tmpl\Configurations\default.tmpl.php */ ?>
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

use WPSpeed\Platform\Plugin;
use WPSpeed\Core\Admin\Icons;

$oParams            = Plugin::getPluginParams();
$hiddenContainsGF   = $oParams->get( 'hidden_containsgf', '' );

?>
<form action="options.php" method="post" id="wpspeed-settings-form">
	<div>
		<div class="savebtn icon"><?php echo submit_button('Save Settings', 'primary', 'wpspeed_settings_submit'); ?></div>
		<div class="icons-container">
			<?php echo Icons::printIconsHTML(Icons::compileUtilityIcons(Icons::getUtilityArray(['browsercaching', 'restorehtaccess', 'orderplugins', 'keycache']))); ?>

			<?php echo Icons::printIconsHTML(Icons::compileUtilityIcons(Icons::getUtilityArray(['cleancache']))); ?>

			<span class="badge bg-dark"><span class="fas fa-chart-area"></span> <?php echo sprintf( __('Cache files: %s', 'wpspeed'), $this->no_files ); ?></span>
			<span class="badge bg-dark"><span class="fas fa-chart-bar"></span> <?php echo sprintf( __('Cache size: %s', 'wpspeed'), $this->size ); ?></span>
		</div>
	</div>
	
    <div class="clearfix-both grid mt-n3">
        <div class="g-col-12">
            <ul class="nav flex-wrap flex-md-row nav-pills">
                <li class="nav-item">
                    <a class="nav-link" href="#autoconfiguration-tab" data-bs-toggle="tab">
                        <div>
                            <div class="fs-6 mb-1"><?php echo __('Auto Configuration', 'wpspeed'); ?></div>
                        </div>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#general-tab" data-bs-toggle="tab">
                        <div>
                            <div class="fs-6 mb-1"><?php echo __('General', 'wpspeed'); ?></div>
                        </div>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#assets-inclusions" data-bs-toggle="tab">
                        <div>
                            <div class="fs-6 mb-1"><?php echo __('Assets Inclusions', 'wpspeed'); ?></div>
                        </div>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#assets-exclusions" data-bs-toggle="tab">
                        <div>
                            <div class="fs-6 mb-1"><?php echo __('Assets Exclusions', 'wpspeed'); ?></div>
                        </div>
                    </a>
                </li>
				<li class="nav-item">
                    <a class="nav-link" href="#lazy-load-tab" data-bs-toggle="tab">
                        <div>
                            <div class="fs-6 mb-1"><?php echo __('Lazy Load', 'wpspeed'); ?></div>
                        </div>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#optimize-image-tab" data-bs-toggle="tab">
                        <div>
                            <div class="fs-6 mb-1"><?php echo __('Optimize Images', 'wpspeed'); ?></div>
                        </div>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#adaptive-contents-tab" data-bs-toggle="tab">
                        <div>
                            <div class="fs-6 mb-1"><?php echo __('Adaptive Contents', 'wpspeed'); ?></div>
                        </div>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#page-cache-tab" data-bs-toggle="tab">
                        <div>
                            <div class="fs-6 mb-1"><?php echo __('Page Cache', 'wpspeed'); ?></div>
                        </div>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#remove-tab" data-bs-toggle="tab">
                        <div>
                            <div class="fs-6 mb-1"><?php echo __('Assets Management', 'wpspeed'); ?></div>
                        </div>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#http2-tab" data-bs-toggle="tab">
                        <div>
                            <div class="fs-6 mb-1"><?php echo __('Http/2', 'wpspeed'); ?></div>
                        </div>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#media-tab" data-bs-toggle="tab">
                        <div>
                            <div class="fs-6 mb-1"><?php echo __('Combine Images', 'wpspeed'); ?></div>
                        </div>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#cdn-tab" data-bs-toggle="tab">
                        <div>
                            <div class="fs-6 mb-1"><?php echo __('CDN', 'wpspeed'); ?></div>
                        </div>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#miscellaneous-tab" data-bs-toggle="tab">
                        <div>
                            <div class="fs-6 mb-1"><?php echo __('Advanced','wpspeed'); ?></div>
                        </div>
                    </a>
                </li>
            </ul>
        </div>
        <div class="g-col-12">
            <?php echo \WPSpeed\Admin\Settings\TabContent::start(); ?>


            <?php echo settings_fields('wpspeedOptionsPage'); ?>

            <?php echo do_settings_sections('wpspeedOptionsPage'); ?>


            <?php echo \WPSpeed\Admin\Settings\TabContent::end(); ?>

            
			<div class="savebtn icon"><?php echo submit_button('Save Settings', 'primary', 'wpspeed_settings_submit'); ?></div>
            <input type="hidden" id="wpspeed_settings_hidden_containsgf"
                   name="wpspeed_settings[hidden_containsgf]"
                   value="<?php echo $hiddenContainsGF; ?>">
            <input type="hidden" id="wpspeed_settings_hidden_api_secret"
                   name="wpspeed_settings[hidden_api_secret]"
                   value="11e603aa">
        </div>
    </div>
</form>