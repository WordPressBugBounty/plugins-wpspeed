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

use WPSpeed\Core\LightImages;
use WPSFramework\Uri\Uri;
use WPSpeed\Platform\Plugin;
use WPSpeed\Platform\Utility;
use WPSpeed\Core\SimpleHtmlDom;
use WPSpeed\Core\SimpleHtmlDomNode;

class Html extends Base
{
        use \WPSpeed\RegexTokenizer\Html;

        public $_html = '';
        protected $_isXhtml = false;
        protected $_isHtml5 = false;
        protected $_cssMinifier = null;
        protected $_jsMinifier = null;
        protected $_jsonMinifier = null;
        protected $_minifyLevel = 0;
        protected $params = null;

        /**
         * "Minify" an HTML page
         *
         * @param   string  $html
         *
         * @param   array   $options
         *
         * 'cssMinifier' : (optional) callback function to process content of STYLE
         * elements.
         *
         * 'jsMinifier' : (optional) callback function to process content of SCRIPT
         * elements. Note: the type attribute is ignored.
         *
         * 'xhtml' : (optional boolean) should content be treated as XHTML1.0? If
         * unset, minify will sniff for an XHTML doctype.
         *
         * @return string
         */
        public static function optimize( $html, $options = array() )
        {
                $options[ 'html' ] = $html;

                $min = new Html( $options );

                try
                {
                        return $min->_optimize();
                }
                catch ( \Exception $e )
                {
                        return $min->_html;
                }
        }

        /**
         * Minify the markup given in the constructor
         *
         * @return string
         * @throws \Exception
         */
        private function _optimize()
        {
                $this->params = Plugin::getPluginParams();
                
                $x  = self::HTML_COMMENT();
                $s1 = self::DOUBLE_QUOTE_STRING();
                $s2 = self::SINGLE_QUOTE_STRING();
                $a  = self::HTML_ATTRIBUTE_CP();

                //Regex for escape elements
                $pr = self::HTML_ELEMENT( 'pre' );
                $sc = self::HTML_ELEMENT( 'script' );
                $st = self::HTML_ELEMENT( 'style' );
                $tx = self::HTML_ELEMENT( 'textarea' );

                if ( $this->_minifyLevel > 0 )
                {
                        //Remove comments (not containing IE conditional comments)
                        $rx          = "#(?><?[^<]*+(?>$pr|$sc|$st|$tx|<!--\[(?><?[^<]*+)*?"
                                . "<!\s*\[(?>-?[^-]*+)*?--!?>|<!DOCTYPE[^>]++>)?)*?\K(?:$x|$)#i";
                        $this->_html = $this->_replace( $rx, '', $this->_html, 'html1' );
                }

                //Reduce runs of whitespace outside all elements to one
                $rx          = "#(?>[^<]*+(?:$pr|$sc|$st|$tx|$x|<(?>[^>=]*+(?:=\s*+(?:$s1|$s2|['\"])?|(?=>)))*?>)?)*?\K"
                        . '(?:[\t\f ]++(?=[\r\n]\s*+<)|(?>\r?\n|\r)\K\s++(?=<)|[\t\f]++(?=[ ]\s*+<)|[\t\f]\K\s*+(?=<)|[ ]\K\s*+(?=<)|$)#i';
                $this->_html = $this->_replace( $rx, '', $this->_html, 'html2' );

                //Minify scripts
                //invalid scripts
                $nsc = "<script\b(?=(?>\s*+$a)*?\s*+type\s*+=\s*+(?![\"']?(?:text|application)/(?:javascript|[^'\"\s>]*?json)))[^<>]*+>(?><?[^<]*+)*?</\s*+script\s*+>";
                //invalid styles
                $nst         = "<style\b(?=(?>\s*+$a)*?\s*+type\s*+=\s*+(?![\"']?(?:text|(?:css|stylesheet))))[^<>]*+>(?><?[^<]*+)*?</\s*+style\s*>";
                $rx          = "#(?><?[^<]*+(?:$x|$nsc|$nst)?)*?\K"
                        . "(?:(<script\b(?!(?>\s*+$a)*?\s*+type\s*+=\s*+(?![\"']?(?:text|application)/(?:javascript|[^'\"\s>]*?json)))[^<>]*+>)((?><?[^<]*+)*?)(</\s*+script\s*+>)|"
                        . "(<style\b(?!(?>\s*+$a)*?\s*+type\s*+=\s*+(?![\"']?text/(?:css|stylesheet)))[^<>]*+>)((?><?[^<]*+)*?)(</\s*+style\s*+>)|$)#i";
                $this->_html = $this->_replace( $rx, '', $this->_html, 'html3', array( $this, '_minifyCB' ) );

                // Add attribute to hide elements that are set to be lazyloaded
                if($this->params->get('lazyload_html_enable', 0) && $lazyloadHtmlCssSelector = trim($this->params->get('lazyload_html_css_selector', ''))) {
                	$simpleHtmlDomInstance = new SimpleHtmlDom();
                	$simpleHtmlDomInstance->load( $this->_html );
                	
                	foreach ( $simpleHtmlDomInstance->find( $lazyloadHtmlCssSelector ) as $element ) {
                		$element->setAttribute('data-jspeed-dom-lazyload', 1);
                	}
                	
                	$this->_html = $simpleHtmlDomInstance->save();
                }
                
                // ADAPTIVE CONTENTS: remove any matched tag for bots
                // Check for user agent exclusion
                if($this->params->get('adaptive_contents_enable', 0) && $adaptiveContentsCssSelector = trim($this->params->get('adaptive_contents_css_selector', 'script[src],iframe,video'))) {
                	if (isset ( $_SERVER ['HTTP_USER_AGENT'] )) {
                		$user_agent = $_SERVER ['HTTP_USER_AGENT'];
                		$botRegexPattern = array();
                		$botsList = $this->params->get ( 'adaptive_contents_bots_list', array (
                				'lighthouse',
                				'googlebot',
                				'googlebot-mobile',
                				'googlebot-video',
                				'gtmetrix',
                				'baiduspider',
                				'duckduckbot',
                				'twitterbot',
                				'applebot',
                				'semrushbot',
                				'ptst',
                				'ahrefs',
                				'pingdom',
                				'seranking',
                				'moto g power',
                				'rsiteauditor'
                		) );
                		if (! empty ( $botsList )) {
                			foreach ( $botsList as &$bot ) {
                				$bot = preg_quote($bot);
                			}
                			$botRegexPattern = implode('|', $botsList);
                		}
                		
                		$isBot = preg_match("/{$botRegexPattern}/i", $user_agent) || array_key_exists($_SERVER['REMOTE_ADDR'], Utility::$botsIP);
                		if($isBot) {
                			$simpleHtmlDomInstance = new SimpleHtmlDom();
                			$simpleHtmlDomInstance->load( $this->_html );
                			
                			foreach ( $simpleHtmlDomInstance->find( $adaptiveContentsCssSelector ) as $element ) {
                				$element->outertext = '';
                			}
                			
                			$this->_html = $simpleHtmlDomInstance->save();
                		}
                	}
                }
                
                if ( $this->_minifyLevel < 1 )
                {
					return trim( $this->_html );
                }

                //Replace line feed with space (legacy)
                $rx          = "#(?>[^<]*+(?:$pr|$sc|$st|$tx|$x|<(?>[^>=]*+(?:=\s*+(?:$s1|$s2|['\"])?|(?=>)))*?>)?)*?\K"
                        . '(?:[\r\n\t\f]++(?=<)|$)#i';
                $this->_html = $this->_replace( $rx, ' ', $this->_html, 'html4' );

                // remove ws around block elements preserving space around inline elements
                //block/undisplayed elements
                $b = 'address|article|aside|audio|body|blockquote|canvas|dd|div|dl'
                        . '|fieldset|figcaption|figure|footer|form|h[1-6]|head|header|hgroup|html|noscript|ol|output|p'
                        . '|pre|section|style|table|title|tfoot|ul|video';

                //self closing block/undisplayed elements
                $b2 = 'base|meta|link|hr';

                //inline elements
                $i = 'b|big|i|small|tt'
                        . '|abbr|acronym|cite|code|dfn|em|kbd|strong|samp|var'
                        . '|a|bdo|br|map|object|q|script|span|sub|sup'
                        . '|button|label|select|textarea';

                //self closing inline elements
                $i2 = 'img|input';

                $rx          = "#(?>\s*+(?:$pr|$sc|$st|$tx|$x|<(?:(?>$i)\b[^>]*+>|(?:/(?>$i)\b>|(?>$i2)\b[^>]*+>)\s*+)|<[^>]*+>)|[^<]++)*?\K"
                        . "(?:\s++(?=<(?>$b|$b2)\b)|(?:</(?>$b)\b>|<(?>$b2)\b[^>]*+>)\K\s++(?!<(?>$i|$i2)\b)|$)#i";
                $this->_html = $this->_replace( $rx, '', $this->_html, 'html5' );

                //Replace runs of whitespace inside elements with single space escaping pre, textarea, scripts and style elements
                //elements to escape
                $e = 'pre|script|style|textarea';

                $rx          = "#(?>[^<]*+(?:$pr|$sc|$st|$tx|$x|<[^>]++>[^<]*+))*?(?:(?:<(?!$e|!)[^>]*+>)?(?>\s?[^\s<]*+)*?\K\s{2,}|\K$)#i";
                $this->_html = $this->_replace( $rx, ' ', $this->_html, 'html6' );

                //Remove additional ws around attributes
                $rx          = "#(?>\s?(?>[^<>]*+(?:<!(?!DOCTYPE)(?>>?[^>]*+)*?>[^<>]*+)?<|(?=[^<>]++>)[^\s>'\"]++(?>$s1|$s2)?|[^<]*+))*?\K"
                        . "(?>\s\s++|$)#i";
                $this->_html = $this->_replace( $rx, ' ', $this->_html, 'html7' );

                if ( $this->_minifyLevel < 2 )
                {
                        return trim( $this->_html );
                }

                //remove redundant attributes
                $rx          = "#(?:(?=[^<>]++>)|(?><?[^<]*+(?>$x|$nsc|$nst|<(?!(?:script|style|link)|/html>))?)*?"
                        . "<(?:(?:script|style|link)|/html>))(?>[ ]?[^ >]*+)*?\K"
                        . '(?: (?:type|language)=["\']?(?:(?:text|application)/(?:javascript|css)|javascript)["\']?|[^<]*+\K$)#i';
                $this->_html = $this->_replace( $rx, '', $this->_html, 'html8' );

                $j = '<input type="hidden" name="[0-9a-f]{32}" value="1" />';

                //Remove quotes from selected attributes
                if ( $this->_isHtml5 )
                {
                        $ns1 = '"[^"\'`=<>\s]*+(?:[\'`=<>\s]|(?<=\\\\)")(?>(?:(?<=\\\\)")?[^"]*+)*?(?<!\\\\)"';
                        $ns2 = "'[^'\"`=<>\s]*+(?:[\"`=<>\s]|(?<=\\\\)')(?>(?:(?<=\\\\)')?[^']*+)*?(?<!\\\\)'";

                        $rx          = "#(?:(?=[^>]*+>)|<[a-z0-9]++ )"
                                . "(?>[=]?[^=><]*+(?:=(?:$ns1|$ns2)|>(?>[^<]*+(?:$j|$x|$nsc|$nst|<(?![a-z0-9]++ ))?)*?(?:<[a-z0-9]++ |$))?)*?"
                                . "(?:=\K([\"'])([^\"'`=<>\s]++)\g{1}[ ]?|\K$)#i";
                        $this->_html = $this->_replace( $rx, '$2 ', $this->_html, 'html9' );
                }

                //Remove last whitespace in open tag
                $rx          = "#(?>[^<]*+(?:$j|$x|$nsc|$nst|<(?![a-z0-9]++))?)*?(?:<[a-z0-9]++(?>\s*+[^\s>]++)*?\K"
                        . "(?:\s*+(?=>)|(?<=[\"'])\s++(?=/>))|$\K)#i";
                $this->_html = $this->_replace( $rx, '', $this->_html, 'html10' );
                
                
                // Process even background images embed in the HTML source code
                $this->params = Plugin::getPluginParams();
                if($this->params->get('lightimgs_status', 0) && $this->params->get('optimize_html_background_images', 0)) {
                	$lightImageOptimizer = new LightImages($this->params);
                	$dom = new \DOMDocument('1.0', 'utf-8');
                	$processGIF = $this->params->get('img_support_gif', 0);
                	$imgsRegex = $processGIF ? '/\.jpg|\.jpeg|\.png|\.gif|\.bmp/i' : '/\.jpg|\.jpeg|\.png|\.bmp/i';
                	$uriInstance = Uri::getInstance();
                	$absoluteUri = rtrim($uriInstance->getScheme() . '://' . $uriInstance->getHost(), '/') . '/';
                	$this->_html = preg_replace_callback(
                			'/(url)(\(.*\))/imU',
                			function ($matches) use ($lightImageOptimizer, $dom, $imgsRegex, $absoluteUri) {
                				// Exclude if it's a fully qualified image, allow local absolute URLs
                				if(preg_match('/https?:\/{2}[^\/]+|\/\//i', $matches[2])) {
                					if (trim(substr($matches[2], 1, strlen(Uri::root())), '/') == trim(Uri::root(), '/') ||
                							trim(substr($matches[2], 2, strlen(Uri::root())), '/') == trim(Uri::root(), '/')) {
                								// This is a local url
                								// Remove the URL
                								$matches[2] = str_ireplace(Uri::root(), '', $matches[2]);
                							} else {
                								return $matches[0];
                							}
                				}
                				
                				$innerContents = trim($matches[2], '()');
                				
                				// Evaluate the delimiter character for the returned string
                				if($innerContents[0] == '"' || $innerContents[0] == "'") {
                					$delimiter = $innerContents[0];
                				} else {
                					$delimiter = "'";
                				}
                				
                				$innerContents = trim($innerContents, '\'"');
                				$innerContents = trim($innerContents, '/\\');
                				$innerContents = str_replace('../', '', $innerContents);
                				$innerContents = '/' . $innerContents;
                				
                				// Apply only to jpg, jpeg, png, gif, bmp
                				if(!preg_match($imgsRegex, $matches[2])) {
                					return "url(" . $delimiter . $innerContents . $delimiter . ")";
                				}
                				
                				// Call here the LightImages optimizer for this image, then replace the path with the cached image
                				$element = $dom->createElement('img', '');
                				$element->setAttribute('src', $innerContents);
                				$lightImageOptimizer->optimizeSingleImage($element);
                				$newSrc = $element->getAttribute('src');
                				$newAbsoluteUri = $absoluteUri . ltrim($newSrc, '/');
                				
                				// Check if the image has been processed, otherwise leave it unaltered
                				if(stripos($newAbsoluteUri, 'cache/wpspeed') === false) {
                					return "url(" . $delimiter . $innerContents . $delimiter . ")";
                				}
                				
                				return "url(" . $delimiter . $newAbsoluteUri . $delimiter . ")";
                			},
						$this->_html
					);
                }
                
                

                return trim( $this->_html );
        }

        /**
         * @param $content
         * @param $type
         *
         * @return  string
         */
        public static function cleanScript( $content, $type )
        {
                $s1 = self::DOUBLE_QUOTE_STRING();
                $s2 = self::SINGLE_QUOTE_STRING();
                $b  = self::BLOCK_COMMENT();
                $l  = self::LINE_COMMENT();
                $c  = self::HTML_COMMENT();

                if ( $type == 'css' )
                {
                        return preg_replace(
                                "#(?>[<\]\-]?[^'\"<\]\-/]*+(?>$s1|$s2|$b|$l|/)?)*?\K(?:$c|$)#i", '', $content
                        );
                }
                else
                {
                        return Js::optimize( $content, array( 'prepareOnly' => true ) );
                }
        }

        /**
         *
         * @param   array  $m
         *
         * @return string
         */
        protected function _minifyCB( $m )
        {
                if ( $m[ 0 ] == '' )
                {
                        return $m[ 0 ];
                }

                if ( strpos( $m[ 0 ], 'var google_conversion' ) !== false )
                {
                        return $m[ 0 ];
                }

                $openTag  = isset( $m[ 1 ] ) && $m[ 1 ] != '' ? $m[ 1 ] : ( isset( $m[ 4 ] ) && $m[ 4 ] != '' ? $m[ 4 ] : '' );
                $content  = isset( $m[ 2 ] ) && $m[ 2 ] != '' ? $m[ 2 ] : ( isset( $m[ 5 ] ) && $m[ 5 ] != '' ? $m[ 5 ] : '' );
                $closeTag = isset( $m[ 3 ] ) && $m[ 3 ] != '' ? $m[ 3 ] : ( isset( $m[ 6 ] ) && $m[ 6 ] != '' ? $m[ 6 ] : '' );

                if ( trim( $content ) == '' )
                {
                        return $m[ 0 ];
                }

                $type = stripos( $openTag, 'script' ) == 1 ? ( stripos( $openTag, 'json' ) !== false ? 'json' : 'js' ) : 'css';

                if ( $this->{'_' . $type . 'Minifier'} )
                {
                        // minify
                        $content = $this->_callMinifier( $this->{'_' . $type . 'Minifier'}, $content );

                        return $this->_needsCdata( $content, $type ) ? "{$openTag}/*<![CDATA[*/{$content}/*]]>*/{$closeTag}" : "{$openTag}{$content}{$closeTag}";
                }
                else
                {
                        return $m[ 0 ];
                }
        }

        /**
         *
         * @param   array   $aFunc
         * @param   string  $content
         *
         * @return string
         */
        protected function _callMinifier( $aFunc, $content )
        {
                $class  = $aFunc[ 0 ];
                $method = $aFunc[ 1 ];

                return $class::$method( $content );
        }

        /**
         *
         * @param   string  $str
         *
         * @return string
         */
        protected function _needsCdata( $str, $type )
        {
                return ( $this->_isXhtml && $type == 'js' && preg_match( '#(?:[<&]|\-\-|\]\]>)#', $str ) );
        }

}
