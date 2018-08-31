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

class Activities
{
	/**
	 * produce xml for the list query of activities
	 * @return array $activites
	 */
	public function getActivitiesList($settings)
	{
		
		$xml = '<?xml version="1.0"?>
			<activitiesQuery>
			<linkingStrategy>portalLinkingStrategy</linkingStrategy>
			<ordering>-startDate</ordering>';
		
		//rendering for univie_personal:
		if($settings['renderingPers'] != '') $xml .= '<rendering>' . $settings['renderingPers'] . '</rendering>';
			
		//set locale:
		$xml .= CommonUtilities::getLocale();
		
		//set page size:
		$xml .= CommonUtilities::getPageSize($settings['pageSize']);
		
		//set offset:
		$xml .= CommonUtilities::getOffset($settings['pageSize']);
		
		//classification scheme types:
		if(($settings['narrowByActivitiesType'] == 1) && ($settings['selectorActivitiesType'] != ''))
		{
			$xml .= $this->getActivityTypesXml($settings['selectorActivitiesType']);
		}
		
		//either for organisations or for persons, both must not be submitted:
		$xml .= CommonUtilities::getPersonsOrOrganisationsXml($settings);
		$xml .= '</activitiesQuery>';
		
		$webservice = new WebService;
		$activities = $webservice->getJson('activities', $xml); 
		
		return $activities;
	}	
	
	/**
	 * query for classificationscheme
	 * @return string xml
	 */
	public function getActivityTypesXml($activityTypes)
	{
		$types = explode(',',$activityTypes);
		foreach((array) $types as $type)
		{
			if(strpos($type, "|"))
			{
				$tmp = explode("|", $type); 
				$type = $tmp[0];
			}
			$xml .= '<typeUri>'. $type . '</typeUri>';
		}
		return $xml;
	}
	
	/**
	 * Complete available xml for POST query
	 * @return String xml
	 */
	public function getActivitiesXml()
	{
		$xml = '<?xml version="1.0"?>
		<activitiesQuery>
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
		  <categoryUri>string</categoryUri>
		  <degreeOfRecognitionUri>string</degreeOfRecognitionUri>
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
		  <forResearchOutputs>
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
			<publicationStatus>string</publicationStatus>
			<publicationCategory>string</publicationCategory>
			<peerReviewed>true</peerReviewed>
			<internationalPeerReviewed>true</internationalPeerReviewed>
			<forJournals>
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
			  <title>string</title>
			  <typeUri>string</typeUri>
			  <issn>string</issn>
			  <workflowStep>string</workflowStep>
			</forJournals>
			<forPublishers>
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
			  <name>string</name>
			  <countryUri>string</countryUri>
			  <workflowStep>string</workflowStep>
			</forPublishers>
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
			<workflowSteps>string</workflowSteps>
		  </forResearchOutputs>
		</activitiesQuery>
		';
	}
}
?>