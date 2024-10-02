<?php
/**
 * @package   WPSpeed
 * @copyright Copyright (c) 2022 JExtensions Store / WPSpeed
 * @license   GNU GPL version 3 or later
 */

namespace WPSFramework\Document;
use WPSFramework\Container\Container;
use WPSFramework\Application\Application;

/**
 * Class Raw
 *
 * Raw output of the document buffer
 *
 * @package WPSFramework\Document
 */
class Raw extends Document
{
	public function __construct(Container $container)
	{
		parent::__construct($container);

		$this->mimeType = 'text/plain';
	}


	/**
	 * It just echoes the output buffer to the browser
	 *
	 * @return  void
	 */
	public function render()
	{
		$this->addHTTPHeader('Content-Type', $this->getMimeType());

		$name = $this->getName();

		if (!empty($name))
		{
			$this->addHTTPHeader('Content-Disposition', 'attachment; filename="' . $name . '"', true);
		}

		$this->outputHTTPHeaders();

		echo $this->getBuffer();
	}
}
