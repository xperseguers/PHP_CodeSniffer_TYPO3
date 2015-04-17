<?php
namespace MyVendor\MyExtension\Utility;

use TYPO3\CMS\Core\Utility\MathUtility as math;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TestUtility {


	static public function whatever() {
		// should be detected as deprecated:
		$res = GeneralUtility::inArray(array(1,2,3), 1);
		// as well
		$res = \TYPO3\CMS\Core\Utility\GeneralUtility::inArray(array(1,2,3), 1);
		// not
		$res = math::canBeInterpretedAsInteger(1);
	}
}
