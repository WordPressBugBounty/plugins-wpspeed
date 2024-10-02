<?php

/**
 * @package   WPSpeed/regextokenizer
 * @copyright Copyright (c) 2022 JExtensions Store / WPSpeed
 * @copyright Copyright (c) 2022 JExtensions Store
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace WPSpeed\RegexTokenizer;

trait Js
{
        use Base;

        public static function JS_HTML_COMMENT()
        {
                return '(?:(?:<!--|(?<=[\s/^])-->)[^\r\n]*+)';
        }
}