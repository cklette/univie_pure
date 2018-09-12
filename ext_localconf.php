<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}


\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Univie.UniviePure',
    'UniviePure',
    array(
        'Pure' => 'list,show',
    )
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:univie_pure/Configuration/TsConfig/ContentElementWizard.txt">');
$TYPO3_CONF_VARS['BE']['AJAX']['univie_pure::ajaxGetPerson'] = 'Univie\\UniviePure\\Utility\\ClassificationScheme->ajaxGetPerson';
$TYPO3_CONF_VARS['BE']['AJAX']['univie_pure::ajaxAddItem'] = 'Univie\\UniviePure\\Utility\\ClassificationScheme->ajaxAddItem';