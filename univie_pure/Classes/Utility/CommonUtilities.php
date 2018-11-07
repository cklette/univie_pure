<?php
namespace Univie\UniviePure\Utility;
use Univie\UniviePure\Service\WebService;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016 Christian Klettner <christian.klettner@univie.ac.at>, univie
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Helpers for all endpoints
 *  
 */
class CommonUtilities 
{
	
	/**
	 * xml for frontend locale
	 * @ return String xml
	 */
	public static function getLocale()
	{
		//TODO: get sys_language_uid, check for allowed languages in service, compare, prepare a fallback
		$lang = ($GLOBALS['TSFE']->config['config']['language'] == 'de') ? 'de_DE' : 'en_GB';
		$xml = '<locale>' . $lang . '</locale>';
		return $xml;
	}
	
	/**
	 * get backend locale
	 * @ return String locale
	 */
	public static function getBackendLanguage()
	{
		return $bl = ($GLOBALS['BE_USER']->uc['lang'] == 'de') ? 'de_DE' : 'en_EN';
	}
	
	/**
	 * page size entered in flexform
	 * @return String xml
	 */
	public static function getPageSize($pageSize)
	{
		if($pageSize == 0 || $pageSize === NULL) $pageSize = 20;
		$xml = '<size>' . $pageSize . '</size>';
		return $xml;
	}
	
	/**
	 * keep track of the counter
	 * @return String xml
	 */
	public static function getOffset($pageSize)
	{
		$offset = (\TYPO3\CMS\Core\Utility\GeneralUtility::_GET('currentPage')) ? \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('currentPage') : 0;
		$offset = ($offset - 1 < 0) ? 0 : $offset - 1;
		$xml = '<offset>' . $offset * $pageSize . '</offset>';
		return $xml;
	}
	
	/**
	 * Either send a request for a unit or for persons
	 * @return String xml
	 */
	public static function getPersonsOrOrganisationsXml($settings)
	{
		//either for organisations or for persons or for projects:
		//If settings.chooseSelector equals 0 => organisational units, case 1 => persons, case 2 => projects:
		switch($settings['chooseSelector'])
		{
			case 0:
				//Resarch-output for organisations:
				$xml = self::getOrganisationsXml($settings);
				break;
			case 1:
				//Research-output for persons:
				$xml = self::getPersonsXml($settings);
				break;
			case 2:
				//Research-output for projects:
				$xml = self::getProjectsXml($settings);
				break;
		}
		return $xml;
	}
	
	/**
	 * Organisations query
	 * @return String xml
	 */
	public static function getOrganisationsXml($settings)
	{
		//if search is entered organisations may be omitted:
		if($settings['selectorOrganisations'] == '' && $settings['narrowBySearch'] != '') return '';
		//otherwise allways write the xml. If organisations are empty nothing is returned from ucris:
		$xml = '<forOrganisationalUnits>';
		$organisations = explode(',',$settings['selectorOrganisations']);
		foreach((array) $organisations as $org)
		{
			if(strpos($org, "|"))
			{
				$tmp = explode("|", $org); 
				$org = $tmp[0];
			}
			$xml .= '<uuids>'. $org . '</uuids>';
			//check for sub units:
			if($settings['includeSubUnits'] == 1)
			{
				$subUnits = self::getSubUnits($org);
				if(is_array($subUnits) && count($subUnits) > 1)
				{
					foreach($subUnits as $subUnit)
					{
						if($subUnit['uuid'] != $org) $xml .= '<uuids>'. $subUnit['uuid'] . '</uuids>';
					}
				}
			}
		}
		$xml .= '</forOrganisationalUnits>';
		return $xml;
	}
	
	/**
	 * Persons query
	 * @return String xml
	 */
	public static function getPersonsXml($settings)
	{
		//if search is entered persons may be omitted:
		if($settings['selectorPersons'] == '' && $settings['narrowBySearch'] != '') return '';
		//otherwise allways write the xml. If persons are empty nothing is returned from ucris:
		$xml = '<forPersons>';
		$persons = explode(',',$settings['selectorPersons']);
		foreach((array) $persons as $person)
		{
			if(strpos($person, "|"))
			{
				$tmp = explode("|", $person); 
				$person = $tmp[0];
			}
			$xml .= '<uuids>'. $person . '</uuids>';
		}
		$xml .= '</forPersons>';
		return $xml;
	}
	
	/**
	 * Projects query
	 * @return String xml
	 */
	 public static function getProjectsXml($settings)
	 {
		if($settings['selectorProjects'] == '' && $settings['narrowBySearch'] != '') return '';
		$xmlProjects = '<?xml version="1.0"?> 
			<projectsQuery> 
				<locale>de_DE</locale> 
				<fields>relatedResearchOutputs.uuid</fields> 
				<ordering>title</ordering> 
				<size>20000</size>';
		$projects = explode(',',$settings['selectorProjects']);
		foreach((array) $projects as $project)
		{
			if(strpos($project, "|"))
			{
				$tmp = explode("|", $project); 
				$project = $tmp[0];
			}
			$xmlProjects .= '<uuids>'. $project . '</uuids>';
		}
		$xmlProjects .= '</projectsQuery>';
		$webservice = new WebService;
		$publications = $webservice->getJson('projects', $xmlProjects); 

		foreach($publications['items'][0]['relatedResearchOutputs'] as $researchOutput)
		{
			$xml .= '<uuids>'. $researchOutput['uuid'] . '</uuids>';
		}
		return $xml;
	 }
	
	/**
	 * query sub organisations for a unit
	 * @return array subUnits Array of all Units connected
	 */
	public static function getSubUnits($orgId)
	{
		$orgName = self::getNameForUuid($orgId);
		$xml = '<?xml version="1.0"?>
				<organisationalUnitsQuery>
					<searchString>' . $orgName . '</searchString>
					<size>1000</size>
					<fields>uuid</fields>
					<ordering>type</ordering>
					<returnUsedContent>true</returnUsedContent>
					<navigationLink>true</navigationLink>
					<organisationalUnitPeriodStatus>ACTIVE</organisationalUnitPeriodStatus>
				</organisationalUnitsQuery>';
		$webservice = new WebService;
		$subUnits = $webservice->getJson('organisational-units',$xml);
		if($subUnits['count'] > 1)
		{
			return $subUnits['items'];
		}
	}
	
	/*
	 * query name by uuid
	 * @return string name
	 */
	public static function getNameForUuid($orgId)
	{
		$xml = '<?xml version="1.0"?>
				<organisationalUnitsQuery>
					<uuids>' . $orgId . '</uuids>
					<fields>name.value</fields>
					<locale>de_DE</locale>
					<organisationalUnitPeriodStatus>ACTIVE</organisationalUnitPeriodStatus>
				</organisationalUnitsQuery>';
		$webservice = new WebService;
		$orgName = $webservice->getJson('organisational-units',$xml);
		if($orgName['count'] == 1)
		{
			return $orgName['items'][0]['name'][0]['value'];
		}
	}
}
?>
