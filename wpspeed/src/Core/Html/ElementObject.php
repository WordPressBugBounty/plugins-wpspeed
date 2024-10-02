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

namespace WPSpeed\Core\Html;

defined( '_WPSPEED_EXEC' ) or die( 'Restricted access' );

class ElementObject
{
	/** @var bool   True if element is self-closing */
	public $bSelfClosing = false;
	/** @var bool   True to capture inside content of elements */
	public $bCaptureContent = false;
	public $bNegateCriteria = false;
	/** @var array  Name or names of element to search for */
	protected $aNames = [ '[a-z0-9]++' ];
	/** @var array  Array of negative criteria to test against the attributes */
	protected $aNegAttrCriteria = [];
	/** @var array  Array of positive criteria to check against the attributes */
	protected $aPosAttrCriteria = [];
	/** @var array  Array of attributes to capture values */
	protected $aCaptureAttributes = [];
	/** @var string|array Regex criteria for target value */
	protected $mValueCriteria = '';

	protected $aCaptureOneOrBothAttributes = [];

	/**
	 * @param $aNames        array    Name(s) of elements to search for
	 */
	public function setNamesArray( $aNames )
	{
		$this->aNames = $aNames;
	}

	public function getNamesArray()
	{
		return $this->aNames;
	}

	public function addNegAttrCriteriaRegex( $sCriteria )
	{
		$this->aNegAttrCriteria[] = $sCriteria;
	}

	public function getNegAttrCriteriaArray()
	{
		return $this->aNegAttrCriteria;
	}

	public function addPosAttrCriteriaRegex( $sCriteria )
	{
		$this->aPosAttrCriteria[] = $sCriteria;
	}

	public function getPosAttrCriteriaArray()
	{
		return $this->aPosAttrCriteria;
	}

	public function setCaptureAttributesArray( $aAttributes )
	{
		$this->aCaptureAttributes = $aAttributes;
	}

	public function getCaptureAttributesArray()
	{
		return $this->aCaptureAttributes;
	}

	public function setValueCriteriaRegex( $mCriteria )
	{
		$this->mValueCriteria = $mCriteria;
	}

	public function getValueCriteriaRegex()
	{
		return $this->mValueCriteria;
	}

	public function setCaptureOneOrBothAttributesArray( $aAttributes)
	{
		$this->aCaptureOneOrBothAttributes = $aAttributes;
	}

	public function getCaptureOneOrBothAttribuesArray()
	{
		return $this->aCaptureOneOrBothAttributes;
	}
}