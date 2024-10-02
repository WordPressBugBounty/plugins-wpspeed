<?php
/**
 * @package   WPSpeed
 * @copyright Copyright (c) 2022 JExtensions Store / WPSpeed
 * @license   GNU GPL version 3 or later
 */

namespace WPSFramework\Container;

use WPSFramework\Application\Application;
use WPSFramework\Mvc\Compiler\Blade;
use WPSFramework\Pimple\Pimple;
use WPSFramework\Session;

/**
 * Dependency injection container for Framework Application
 *
 * @property  string                                         $application_name      The name of the application
 * @property  string                                         $session_segment_name  The name of the session segment
 * @property  string                                         $basePath              The path to your application's PHP files
 * @property  string                                         $templatePath          The base path of all your template folders
 * @property  string                                         $languagePath          The base path of all your language folders
 * @property  string                                         $temporaryPath         The temporary directory of your application
 * @property  string                                         $filesystemBase        The base path of your web root (for use by WPSFramework\Filesystem)
 * @property  string                                         $mediaQueryKey         The query string parameter to append to media added through the Template class
 * @property  string                                         $applicationNamespace  Namespace for the application classes, defaults to \\{$application_name}
 *
 * @property-read  \WPSFramework\Application\Application              $application           The application instance
 * @property-read  \WPSFramework\Application\Configuration            $appConfig             The application configuration registry
 * @property-read  \WPSFramework\Mvc\Compiler\Blade                   $blade                 The Blade view template compiler engine
 * @property-read  \WPSFramework\Event\Dispatcher                     $eventDispatcher       The global event dispatched
 * @property-read  \WPSFramework\Filesystem\FilesystemInterface       $fileSystem            The filesystem manager, created in hybrid mode
 * @property-read  \WPSFramework\Input\Input                          $input                 The global application input object
 * @property-read  \WPSFramework\Router\Router                        $router                The URL router
 * @property-read  \WPSFramework\Session\Segment                      $segment               The session segment, where values are stored
 * @property-read  \WPSFramework\Session\Manager                      $session               The session manager
 */
class Container extends Pimple
{
	public function __construct(array $values = array())
	{
		$this->application_name = '';
		$this->session_segment_name = null;
		$this->basePath = null;
		$this->templatePath = null;
		$this->languagePath = null;
		$this->temporaryPath = null;
		$this->filesystemBase = null;
		$this->sqlPath = null;
		$this->mediaQueryKey = null;

		if ( ! isset( $values['application_name'] ) )
		{
			$values['application_name'] = 'admin';
		}
		
		if ( ! isset( $values['applicationNamespace'] ) )
		{
			$values['applicationNamespace'] = '\\WPSpeed\\Admin';
		}
		
		if ( ! isset( $values['filesystemBase'] ) )
		{
			$values['filesystemBase'] = rtrim( ABSPATH, '/\\' );
		}
		
		if ( ! isset( $values['basePath'] ) )
		{
			$values['basePath'] = WPSPEED_DIR . 'src/Admin';
		}
		
		if ( ! isset( $values['templatePath'] ) )
		{
			$values['templatePath'] = WPSPEED_DIR . 'src/Admin/templates';
		}
		
		if ( ! isset( $values['temporaryPath'] ) )
		{
			$values['temporaryPath'] = WPSPEED_DIR . 'src/Admin/templates/compiled';
		}
		
		if ( ! isset( $values['session_segment_name'] ) )
		{
			$installationId = 'default';
			
			if ( function_exists( 'base64_encode' ) )
			{
				$installationId = base64_encode( __DIR__ );
			}
			
			if ( function_exists( 'md5' ) )
			{
				$installationId = md5( __DIR__ );
			}
			
			if ( function_exists( 'sha1' ) )
			{
				$installationId = sha1( __DIR__ );
			}
			
			$values['session_segment_name'] = $values['application_name'] . '_' . $installationId;
		}
		
		$values['db']              = '';
		$values['fileSystem']      = '';
		$values['session']         = '';
		$values['userManager']     = '';
		
		parent::__construct($values);

		// Application namespace
		if (!isset($this['applicationNamespace']))
		{
			$this['applicationNamespace'] = '\\' . $this->application_name;
		}

		// Application service
		if (!isset($this['application']))
		{
			$this['application'] = function (Container $c)
			{
				return Application::getInstance($c->application_name, $c);
			};
		}

		// Blade view template compiler service
		if (!isset($this['blade']))
		{
			$this['blade'] = function (Container $c)
			{
				return new Blade($c);
			};
		}

		// Application Dispatcher service
		if (!isset($this['dispatcher']))
		{
			$this['dispatcher'] = function (Container $c) {
				$className = $this->applicationNamespace . '\\Dispatcher';
				
				if (!class_exists($className))
				{
					$className = '\\' . ucfirst($c->application_name) . '\Dispatcher';
				}
				
				if (!class_exists($className))
				{
					$className = '\WPSFramework\Dispatcher\Dispatcher';
				}
				
				return new $className($c);
			};
		}
		
		// Filesystem Abstraction Layer service
		if (!isset($this['fileSystem']))
		{
			$this['fileSystem'] = function (Container $c)
			{
				return \WPSFramework\Filesystem\Factory::getAdapter($c, true);
			};
		}

		// Input Access service
		if (!isset($this['input']))
		{
			$this['input'] = function (Container $c)
			{
				return new \WPSFramework\Input\Input();
			};
		}

		// Session Manager service
		if (!isset($this['session']))
		{
			$this['session'] = function ()
			{
				return new Session\Manager(
					new Session\SegmentFactory,
					new Session\CsrfTokenFactory(),
					$_COOKIE
				);
			};
		}

		// Application Session Segment service
		if (!isset($this['segment']))
		{
			$this['segment'] = function (Container $c)
			{
				if (empty($c->session_segment_name))
				{
					$c->session_segment_name = 'WPSFramework\\' . $c->application_name;
				}

				return $c->session->newSegment($c->session_segment_name);
			};
		}
	}
}
