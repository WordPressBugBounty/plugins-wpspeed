<?php
/**
 * @package   WPSpeed
 * @copyright Copyright (c) 2022 JExtensions Store / WPSpeed
 * @license   GNU GPL version 3 or later
 */

namespace WPSFramework\Dispatcher;

use WPSFramework\Container\Container;
use WPSFramework\Input\Input;
use WPSFramework\Application\Application;
use WPSFramework\Mvc;

/**
 * Class Dispatcher
 *
 * A simple application dispatcher
 *
 * @package WPSFramework\Dispatcher
 */
class Dispatcher
{
	/** @var   Input  Input variables */
	protected $input = array();

	/** @var   string  The name of the default view, in case none is specified */
	public $defaultView = 'main';

	/** @var   Container  A copy of the application object we belong to */
	protected $container;

	/** @var string The view which will be rendered by the dispatcher */
	protected $view;

	/** @var string The layout for rendering the view */
	protected $layout;

	/**
	 * Public constructor
	 *
	 * @param   Container $container The container this dispatcher belongs to
	 */
	public function __construct($container = null)
	{
		if (!is_object($container) || !($container instanceof Container))
		{
			$container = Application::getInstance()->getContainer();
		}

		$this->container = $container;

		$this->input = $container->input;

		// Get the default values for the view and layout names
		$this->view = $this->input->getCmd('view', null);
		$this->layout = $this->input->getCmd('layout', null);

		// Not redundant; you may pass an empty but non-null view which is invalid, so we need the fallback
		if (empty($this->view))
		{
			$this->view = $this->defaultView;
			$this->container->input->set('view', $this->view);
		}
	}

	/**
	 * The main code of the Dispatcher. It spawns the necessary controller and
	 * runs it.
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 */
	public function dispatch()
	{
		try
		{
			$result = $this->onBeforeDispatch();
			$error = '';
		}
		catch (\Exception $e)
		{
			$result = false;
			$error = $e->getMessage();
		}

		if (!$result)
		{
			// For json, don't use normal 403 page, but a json encoded message
			if ($this->input->get('format', '') == 'json')
			{
				echo json_encode(array('code' => '403', 'error' => $error));

				$this->container->application->close();
			}

			throw new \Exception('Error', 403);
		}

		// Get and execute the controller
		$view = $this->input->getCmd('view', $this->defaultView);
		$task = $this->input->getCmd('task', 'default');

		if (empty($task))
		{
			$task = 'default';
			$this->input->set('task', $task);
		}

		$controller = Mvc\Controller::getInstance($this->container->application_name, $view, $this->container);
		$status = $controller->execute($task);

		if ($status === false)
		{
			throw new \Exception('Error', 403);
		}

		if (!$this->onAfterDispatch())
		{
			throw new \Exception('Error', 403);
		}

		$controller->redirect();
	}

	/**
	 * Executes right before the dispatcher tries to instantiate and run the
	 * controller.
	 *
	 * @return  boolean  Return false to abort
	 */
	public function onBeforeDispatch()
	{
		return true;
	}

	/**
	 * Executes right after the dispatcher runs the controller.
	 *
	 * @return  boolean  Return false to abort
	 */
	public function onAfterDispatch()
	{
		return true;
	}
}
