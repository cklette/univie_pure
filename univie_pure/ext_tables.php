<?php
$_EXTKEY = 'univie_pure';
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY, 
	'UniviePure', 
	'u:cris Wien Schnittstelle'
);
$extensionName = strtolower(\TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($_EXTKEY));
$pluginName = strtolower($extensionName);
$pluginSignature = $extensionName.'_'.$pluginName;

$TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages,recursive';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:'.$_EXTKEY . '/Configuration/FlexForms/flexform.xml');

// BE AJAX Handler
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler (
'univie_pure::ajaxGetPerson',
'Univie\\UniviePure\\Utility\\ClassificationScheme->ajaxGetPerson'
);

/**
 * ContentElementWizard for Pi1
 */
$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['Univie\UniviePure\Utility\UniviePureWizard'] =
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) .
    'Classes/Utility/UniviePureWizard.php';