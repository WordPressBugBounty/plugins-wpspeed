<?php

/**
 * @package   WPSpeed/minify
 * @copyright Copyright (c) 2022 JExtensions Store / WPSpeed
 * @copyright Copyright (c) 2022 JExtensions Store
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace WPSpeed\Minify;

abstract class Base
{
        use \WPSpeed\RegexTokenizer\Base;

	protected function __construct($options)
	{
		foreach ($options as $key => $value)
		{
			$this->{'_' . $key} = $value;
		}

		if (!defined('WPSPEED_MINIFY_CONFIGURED'))
		{
			ini_set('pcre.backtrack_limit', 1000000);
			ini_set('pcre.recursion_limit', 1000000);
			ini_set('pcre.jit', 0);

			define('WPSPEED_MINIFY_CONFIGURED', 1);
		}
	}

	/**
	 *
	 * @staticvar bool $tm
	 *
	 * @param   string    $regex
	 * @param   string    $replacement
	 * @param   string    $code
	 * @param   mixed     $regex_num
	 * @param   callable  $callback
	 *
	 * @return string
	 * @throws \Exception
	 */
	protected function _replace($regex, $replacement, $code, $regex_num, $callback = null)
	{
		static $tm = false;

		if ($tm === false)
		{
			$this->_debug('', '');
			$tm = true;
		}

		if (empty($callback))
		{
			$op_code = preg_replace($regex, $replacement, $code);
		}
		else
		{
			$op_code = preg_replace_callback($regex, $callback, $code);
		}

		$this->_debug($regex, $code, $regex_num);

		self::throwExceptionOnPregError();

		return $op_code;
	}

}
