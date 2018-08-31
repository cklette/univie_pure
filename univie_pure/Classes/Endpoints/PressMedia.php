<?php
namespace Univie\UniviePure\Endpoints;
use Univie\UniviePure\Service\WebService;
use Univie\UniviePure\Utility\CommonUtilities;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/*
 * (c) 2016 Christian Klettner <christian.klettner@univie.ac.at>, univie
 *
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

class PressMedia{
	
	/**
	 * produce xml for the list query of press-media
	 * @return array $pressMedia
	 */
	public function getPressMediaList($settings)
	{
		$xml = '<?xml version="1.0"?>
			<pressMediaQuery>
			<linkingStrategy>portalLinkingStrategy</linkingStrategy>
			<ordering>-date</ordering>';
		//set locale:
		$xml .= CommonUtilities::getLocale();
		
		//set page size:
		$xml .= CommonUtilities::getPageSize($settings['pageSize']);
		
		//set offset:
		$xml .= CommonUtilities::getOffset($settings['pageSize']);
		
		//either for organisations or for persons, both must not be submitted:
		$xml .= CommonUtilities::getPersonsOrOrganisationsXml($settings);
		$xml .= '</pressMediaQuery>';

		$webservice = new WebService;
		$pressMedia = $webservice->getJson('press-media', $xml);
		return $pressMedia;
	}
	
	/**
	 * Complete available xml for POST query
	 * @return String xml
	 */
	public function getPressMediaXml()
	{
		$xml = '<?xml version="1.0"?>
			<pressMediaQuery>
			  <searchString>string</searchString>
			  <uuids>string</uuids>
			  <size>1</size>
			  <offset>1</offset>
			  <linkingStrategy>string</linkingStrategy>
			  <locale>string</locale>
			  <fallbackLocale>string</fallbackLocale>
			  <rendering>string</rendering>
			  <fields>string</fields>
			  <ordering>string</ordering>
			  <returnUsedContent>true</returnUsedContent>
			  <navigationLink>true</navigationLink>
			  <typeUri>string</typeUri>
			  <period>
				<startDate>
				  <year>1</year>
				  <month>1</month>
				  <day>1</day>
				</startDate>
				<endDate>
				  <year>1</year>
				  <month>1</month>
				  <day>1</day>
				</endDate>
			  </period>
			  <workflowStep>string</workflowStep>
			  <managingOrganisationalUnits>
				<searchString>string</searchString>
				<uuids>string</uuids>
				<size>1</size>
				<offset>1</offset>
				<linkingStrategy>string</linkingStrategy>
				<locale>string</locale>
				<fallbackLocale>string</fallbackLocale>
				<rendering>string</rendering>
				<fields>string</fields>
				<ordering>string</ordering>
				<returnUsedContent>true</returnUsedContent>
				<navigationLink>true</navigationLink>
				<organisationalUnitTypeUri>string</organisationalUnitTypeUri>
				<organisationalUnitPeriodStatus>ACTIVE</organisationalUnitPeriodStatus>
			  </managingOrganisationalUnits>
			  <forOrganisationalUnits>
				<searchString>string</searchString>
				<uuids>string</uuids>
				<size>1</size>
				<offset>1</offset>
				<linkingStrategy>string</linkingStrategy>
				<locale>string</locale>
				<fallbackLocale>string</fallbackLocale>
				<rendering>string</rendering>
				<fields>string</fields>
				<ordering>string</ordering>
				<returnUsedContent>true</returnUsedContent>
				<navigationLink>true</navigationLink>
				<organisationalUnitTypeUri>string</organisationalUnitTypeUri>
				<organisationalUnitPeriodStatus>ACTIVE</organisationalUnitPeriodStatus>
			  </forOrganisationalUnits>
			  <forPersons>
				<searchString>string</searchString>
				<uuids>string</uuids>
				<size>1</size>
				<offset>1</offset>
				<linkingStrategy>string</linkingStrategy>
				<locale>string</locale>
				<fallbackLocale>string</fallbackLocale>
				<rendering>string</rendering>
				<fields>string</fields>
				<ordering>string</ordering>
				<returnUsedContent>true</returnUsedContent>
				<navigationLink>true</navigationLink>
				<employmentTypeUri>string</employmentTypeUri>
				<employmentStatus>ACTIVE</employmentStatus>
				<employmentPeriod>
				  <startDate>
					<year>1</year>
					<month>1</month>
					<day>1</day>
				  </startDate>
				  <endDate>
					<year>1</year>
					<month>1</month>
					<day>1</day>
				  </endDate>
				</employmentPeriod>
				<associationType>STAFF</associationType>
				<forOrganisations>
				  <searchString>string</searchString>
				  <uuids>string</uuids>
				  <size>1</size>
				  <offset>1</offset>
				  <linkingStrategy>string</linkingStrategy>
				  <locale>string</locale>
				  <fallbackLocale>string</fallbackLocale>
				  <rendering>string</rendering>
				  <fields>string</fields>
				  <ordering>string</ordering>
				  <returnUsedContent>true</returnUsedContent>
				  <navigationLink>true</navigationLink>
				  <organisationalUnitTypeUri>string</organisationalUnitTypeUri>
				  <organisationalUnitPeriodStatus>ACTIVE</organisationalUnitPeriodStatus>
				</forOrganisations>
			  </forPersons>
			</pressMediaQuery>';
	}
}
?>