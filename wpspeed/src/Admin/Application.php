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
namespace WPSpeed\Admin;

use WPSFramework\Container\Container;

class Application extends \WPSFramework\Application\Application {
	/**
	 * Public constructor
	 *
	 * @param Container $container
	 *        	Configuration parameters
	 *        	
	 * @return void
	 */
	public function __construct(?Container $container = null) {
		// Start keeping time
		$this->startTime = microtime ( true );

		// Create or attach the DI container
		if (! is_object ( $container ) || ! ($container instanceof Container)) {
			$container = new Container ();
		}

		$this->container = $container;

		// Set the application name
		if (empty ( $container ['application_name'] )) {
			$container->application_name = $this->getName ();
		}

		$this->name = $container->application_name;

		// Set up the filesystem path
		if (empty ( $container ['filesystemBase'] )) {
			$container->filesystemBase = APATH_BASE;
		}

		// Set up the base path
		if (empty ( $container ['basePath'] )) {
			$container->basePath = (defined ( 'APATH_BASE' ) ? APATH_BASE : $container->filesystemBase) . '/' . ucfirst ( $this->name );
		}

		// Set up the template path
		if (empty ( $container ['templatePath'] )) {
			$container->templatePath = defined ( 'APATH_THEMES' ) ? APATH_THEMES : $container->filesystemBase . '/src/Admin/templates';
		}

		// Set up the temporary path
		if (empty ( $container ['temporaryPath'] )) {
			$container->temporaryPath = defined ( 'APATH_TMP' ) ? APATH_TMP . '/src/Admin/templates/compiled' : $container->filesystemBase . '/src/Admin/templates/compiled';
		}

		// Set up the language path
		if (empty ( $container ['languagePath'] )) {
			$container->languagePath = defined ( 'APATH_TRANSLATION' ) ? APATH_TRANSLATION : $container->filesystemBase . '/languages';
		}

		// Set up the language path
		if (empty ( $container ['sqlPath'] )) {
			$container->sqlPath = defined ( 'APATH_ROOT' ) ? (APATH_ROOT . '/installation/sql') : $container->filesystemBase . '/installation/sql';
		}

		// Set up the template
		$this->setTemplate ();
	}
	public function initialise() {
		$this->setTemplate ( 'admin' );
	}
	public function route($url = null) {
		$view = $this->container->input->get ( 'view', 'configurations' );
		$this->container->input->set ( 'view', $view );

		$task = $this->container->input->get ( 'task', 'default' );
		$utilityTasks = [ 
				'browsercaching',
				'restorehtaccess',
				'orderplugins',
				'keycache',
				'cleancache',
				'restoreimages',
				'deletebackups'
		];

		if (in_array ( $task, $utilityTasks )) {
			$this->container->input->set ( 'view', 'utility' );
		}
	}

	/**
	 * Redirect to another URL.
	 *
	 * Optionally enqueues a message in the system message queue (which will be displayed
	 * the next time a page is loaded) using the enqueueMessage method.
	 *
	 * @param string $url
	 *        	The URL to redirect to. Can only be http/https URL
	 * @param string $msg
	 *        	An optional message to display on redirect.
	 * @param string $msgType
	 *        	An optional message type. Defaults to message.
	 * @param boolean $moved
	 *        	True if the page is 301 Permanently Moved, otherwise 303 See Other is assumed.
	 *        	
	 * @return void Calls exit().
	 *        
	 * @see Application::enqueueMessage()
	 */
	public function redirect($url, $msg = '', $msgType = 'info', $moved = false) {

		// If the message exists, enqueue it.
		if (is_string ( $msg ) && trim ( $msg )) {
			$this->enqueueMessage ( $msg, $msgType );
		}

		// Persist messages if they exist.
		if (count ( $this->messageQueue )) {
			$this->setMessageInTransient ( $this->messageQueue );
		}

		// If the headers have been sent, then we cannot send an additional location header
		// so we will output a javascript redirect statement.
		if (headers_sent ()) {
			$url = htmlspecialchars ( $url );
			$url = str_replace ( '&amp;', '&', $url );
			echo "<script>document.location.href='" . $url . "';</script>\n";
		} else {
			wp_safe_redirect ( admin_url ( $url ) );
		}

		exit ( 0 );
	}
	public function setMessageInTransient($messages) {
		if ($existingMessages = get_transient ( 'wpspeed_notices' )) {
			$messages = array_merge ( $existingMessages, $messages );
		}

		set_transient ( 'wpspeed_notices', $messages, 60 * 5 );
	}

	/**
	 * Method to close the application.
	 * Automatically commits the session.
	 *
	 * @param integer $code
	 *        	The exit code (optional; default is 0).
	 *        	
	 * @return void
	 */
	public function close($code = 0) {
		// Persist messages if they exist.
		if (count ( $this->messageQueue )) {
			$this->setMessageInTransient ( $this->messageQueue );
		}

		exit ( $code );
	}

	/**
	 * Get the system message queue.
	 *
	 * @return array The system message queue.
	 */
	public function getMessageQueue() {
		// For empty queue, if messages exists in the session, enqueue them.
		if (! count ( $this->messageQueue )) {
			if ($messages = get_transient ( 'wpspeed_notices' )) {
				$this->messageQueue = $messages;
			}

			delete_transient ( 'wpspeed_notices' );
		}

		return $this->messageQueue;
	}
	public function publishMessages($messages) {
		foreach ( $messages as $message ) {
			echo <<<HTML
<div class="wpspeed-notice notice notice-{$message['type']} is-dismissible"><p>{$message['message']}</p></div>
HTML;
		}
	}
}