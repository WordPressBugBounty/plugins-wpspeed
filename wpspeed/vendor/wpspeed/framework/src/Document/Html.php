<?php
/**
 * @package   WPSpeed
 * @copyright Copyright (c) 2022 JExtensions Store / WPSpeed
 * @license   GNU GPL version 3 or later
 */

namespace WPSFramework\Document;

/**
 * Class Html
 *
 * The HTML document implementation. Uses the defined template to render itself.
 *
 * @package WPSFramework\Document
 */
class Html extends Document
{
	/**
	 * Uses the defined template to outputs the buffer to the browser using the
	 * defined template.
	 *
	 * @return  void
	 */

	public function render()
	{
		$this->addHTTPHeader('Content-Type', $this->getMimeType());

		$name = $this->getName();

		if (!empty($name))
		{
			$this->addHTTPHeader('Content-Disposition', 'attachment; filename="' . $name . '.html"', true);
		}

		$template = $this->container->application->getTemplate();
		$templatePath = $this->container->application->getContainer()->templatePath . '/' . $template;

		include $templatePath . '/index.php';
	}
}
