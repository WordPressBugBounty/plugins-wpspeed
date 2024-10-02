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

trait Css
{
        use Base;

        //language=RegExp
        public static function CSS_IDENT()
        {
                return '(?:\\\\.|[a-z0-9_-]++\s++)';
        }

        //language=RegExp
        public static function CSS_URL_CP( $bCV = false )
        {
                $sCssUrl = '(?:url\(|(?<=url)\()(?:\s*+[\'"])?<<' . self::CSS_URL_VALUE() . '>>(?:[\'"]\s*+)?\)';

                return self::prepare( $sCssUrl, $bCV );
        }

        //language=RegExp
        public static function CSS_URL_VALUE()
        {
                return '(?:' . self::STRING_VALUE() . '|' . self::CSS_URL_VALUE_UNQUOTED() . ')';
        }

        //language=RegExp
        public static function CSS_URL_VALUE_UNQUOTED()
        {
                return '(?<=url\()(?>\s*+(?:\\\\.)?[^\\\\()\s\'"]*+)++';
        }

}

