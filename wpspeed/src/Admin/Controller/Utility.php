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

namespace WPSpeed\Admin\Controller;

use WPSFramework\Container\Container;
use WPSFramework\Mvc\Controller;
use WPSpeed\Dispatcher;
use WPSpeed\Core\Admin\Tasks;
use WPSpeed\Platform\Cache;
use WPSpeed\Platform\Plugin;

class Utility extends Controller
{
	public function __construct( ?Container $container = null )
	{
		parent::__construct( $container );
	}

	public function browsercaching()
	{
		if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'browsercaching')) {
			wp_die(__( 'Not authorized' ));
		}
		
		$expires = Tasks::leverageBrowserCaching();

		if ( $expires === false )
		{
			$this->setMessage( __( 'Failed trying to add browser caching codes to the .htaccess file', 'wpspeed' ), 'error' );
		}
		elseif ( $expires === 'FILEDOESNTEXIST' )
		{
			$this->setMessage( __( 'No .htaccess file were found in the root of this site', 'wpspeed' ), 'warning' );
		}
		elseif ( $expires === 'CODEUPDATEDSUCCESS' )
		{
			$this->setMessage( __( 'The .htaccess file was updated successfully', 'wpspeed' ), 'success' );
		}
		elseif ( $expires === 'CODEUPDATEDFAIL' )
		{
			$this->setMessage( __( 'Failed to update the .htaccess file', 'wpspeed' ), 'warning' );
		}
		elseif ( $expires === 'CODEALREADYINFILE' )
		{
			$this->setMessage( __( 'Optimizations already added to the .htaccess', 'wpspeed' ), 'warning' );
		}
		else
		{
			$this->setMessage( __( 'Optimizations for the htaccess file have been successfully added, browser caching is now enabled for all assets', 'wpspeed' ), 'success' );
		}

		$this->setRedirect( 'options-general.php?page=wpspeed' );

		$this->redirect();
	}

	public function restorehtaccess()
	{
		if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'restorehtaccess')) {
			wp_die(__( 'Not authorized' ));
		}
		
		$cleanedHtaccess = Tasks::cleanHtaccess();
		
		if ( $cleanedHtaccess === false )
		{
			$this->setMessage( __( 'Failed trying to restore the original .htaccess file', 'wpspeed' ), 'error' );
		}
		else
		{
			$this->setMessage( __( 'Optimizations for the htaccess file have been successfully removed, browser caching is now disabled for all assets', 'wpspeed' ), 'success' );
		}
		
		$this->setRedirect( 'options-general.php?page=wpspeed' );
		
		$this->redirect();
	}
	
	public function orderplugins()
	{
		if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'orderplugins')) {
			wp_die(__( 'Not authorized' ));
		}
		
		Dispatcher::orderPlugin();

		$this->setMessage( __( 'Plugins ordered successfully', 'wpspeed' ), 'success' );
		$this->setRedirect( 'options-general.php?page=wpspeed' );

		$this->redirect();
	}

	public function keycache()
	{
		if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'keycache')) {
			wp_die(__( 'Not authorized' ));
		}
		
		Tasks::generateNewCacheKey();

		$this->setMessage( __( 'New cache key generated!', 'wpspeed' ), 'success' );
		$this->setRedirect( 'options-general.php?page=wpspeed' );

		$this->redirect();
	}

	public function cleancache()
	{
		if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'cleancache')) {
			wp_die(__( 'Not authorized' ));
		}
		
		if ( Cache::deleteCache() )
		{
			$this->setMessage( __( 'Cache deleted successfully!', 'wpspeed' ), 'success' );
			if(Plugin::getPluginParams()->get('clear_server_cache', 0)) {
				$this->setMessage( __( 'The cache has been deleted and server cache has been successfully purged!', 'wpspeed' ), 'success' );
			}
		}
		else
		{
			$this->setMessage( __( 'Error cleaning cache!', 'wpspeed' ), 'error' );
		}

		if ( ( $return = $this->input->get( 'return', '' ) ) != '' )
		{
			$this->setRedirect( base64_decode_url( $return ) );
		}
		else
		{
			$this->setRedirect( 'options-general.php?page=wpspeed' );
		}

		$this->redirect();
	}
}