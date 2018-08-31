<?php
namespace Univie\UniviePure\Endpoints;
use Univie\UniviePure\Service\WebService;
use Univie\UniviePure\Utility\CommonUtilities;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/*
 * (c) 2017 Christian Klettner <christian.klettner@univie.ac.at>, univie
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

class ResearchOutput
{
	
	/**
	 * produce xml for the list query of research-output
	 * @return array $publications
	 */
	public function getPublicationList($settings)
	{
						
		$xml = '<?xml version="1.0"?>
				<researchOutputsQuery>
				<linkingStrategy>portalLinkingStrategy</linkingStrategy>
				<ordering>-publicationYear</ordering>
				<returnUsedContent>true</returnUsedContent>
				<navigationLink>true</navigationLink>
				';
		
		//set locale:
		$xml .= CommonUtilities::getLocale();
		
		//set page size:
		$xml .= CommonUtilities::getPageSize($settings['pageSize']);
		
		//set offset:
		$xml .= CommonUtilities::getOffset($settings['pageSize']);
		
		//ordering:
		if(!array_key_exists('researchOutputOrdering',$settings) 
			|| strlen($settings['researchOutputOrdering']) == 0) $settings['researchOutputOrdering'] = '-publicationYear';//backwardscompatibility
		$xml .= '<ordering>' . $settings['researchOutputOrdering'] . '</ordering>';
		
		//Do we need the current status?
		$xml .= '<fields>uuid</fields><fields>publicationStatuses.current</fields>';
		
		//grouping:
		if($settings['groupByYear'] == 1)
		{
			$xml .= $this->getFieldForGrouping();
		}
		
		//show publication type:
		if($settings['showPublicationType'] == 1)
		{
			$xml .= $this->getFieldForPublicationType();
		}
		
		//peer-reviewed:
		if($settings['peerReviewedOnly'] == 1)
		{
			$xml .= '<peerReviewed>true</peerReviewed>';
		}
		
		//published after date:
		if($settings['publishedAfterDate'])
		{
			$xml .= '<publishedAfterDate>' . $settings['publishedAfterDate'] . '</publishedAfterDate>';
		}
		
		//published before date:
		if($settings['publishedBeforeDate'])
		{
			$xml .= '<publishedBeforeDate>' . $settings['publishedBeforeDate'] . '</publishedBeforeDate>';
		}
		
		//search AND filter:
		if($settings['narrowBySearch'] || $settings['filter'])
		{
			$xml .= $this->getSearchXml($settings);
		}
		
		//rendering:
		$xml .= '<rendering>' . $settings['rendering'] . '</rendering>';
		
		//classification scheme types:
		if(($settings['narrowByPublicationType'] == 1) && ($settings['selectorPublicationType'] != ''))
		{
			$xml .= $this->getResearchTypesXml($settings['selectorPublicationType']);
		}
				
		//either for organisations or for persons, both must not be submitted:
		$xml .= CommonUtilities::getPersonsOrOrganisationsXml($settings);
		
		$xml .= '</researchOutputsQuery>';
		
		$webservice = new WebService;
		$publications = $webservice->getJson('research-outputs', $xml); 
		//reduce the array to year, status, rendering, uuid:
		$publications = $this->transformArray($publications, $settings);
		return $publications;

	}
	
	/*
	 * Get the year for grouping
	 * @return string xml
	 */
	public function getFieldForGrouping()
	{
		$xml = '<fields>publicationStatuses.publicationDate.year</fields>';
		return $xml;
	}
	
	/*
	 * get the publication type (value, uri)
	 * @return string xml
	 */
	public function getFieldForPublicationType()
	{
		$xml = '<fields>publicationStatuses.publicationStatus.*</fields>';
		return $xml;
	}
	
	/**
	 * xml for search string
	 * @return string xml
	 */
	public function getSearchXml($settings)
	{
		$terms = $settings['narrowBySearch'];
		//combine the backend filter and the frontend form:
		if($settings['filter']) $terms .= ' ' . $settings['filter'];
		$xml .= '<searchString>' . trim($terms) . '</searchString>';
		return $xml;
	}
	
	/**
	 * query for classificationscheme
	 * @return string xml
	 */
	public function getResearchTypesXml($researchTypes)
	{
		$types = explode(',',$researchTypes);
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
	 * result set for manually chosen persons
	 * @return string xml
	 */
	public function getPersonsXml($personsList)
	{
		$xml = '<forPersons>';
		$persons = explode(',',$personsList);
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
	 * result set for organisational units
	 * @return string xml
	 */
	public function getOrganisationsXml($organisationList)
	{
		$xml = '<forOrganisationalUnits>';
		$organisations = explode(',',$organisationList);
		foreach((array) $organisations as $org)
		{
			if(strpos($org, "|"))
			{
				$tmp = explode("|", $org); 
				$org = $tmp[0];
			}
			$xml .= '<uuids>'. $org . '</uuids>';
		}
		$xml .= '</forOrganisationalUnits>';
		return $xml;
	}
	
	/**
	 * restructure array: group by year
	 * @return array array
	 */
	public function groupByYear($publications)
	{
		$sortkey = $publications['contributionToJournal']['publicationStatuses']['publicationStatus']['publicationDate']['year'];
		$array = array();
		$array['count'] = $publications['count'];
		$i = 0;
		foreach ($publications['items'] as $contribution)
		{
			$array['contributionToJournal'][$i]['year'] = $contribution['publicationStatuses']['publicationDate']['year'];
			$array['contributionToJournal'][$i]['rendering'] = $contribution['rendering'][0]['value'];
			$array['contributionToJournal'][$i]['uuid'] = $contribution['uuid'];
			$i++;
		}
		return $array;
	}
	
	/**
	 * restructure array 
	 * @return array array
	 */
	public function transformArray($publications, $settings)
	{
		
		$array = array();
		$array['count'] = $publications['count'];
		$i = 0;
		foreach ($publications['items'] as $contribution)
		{
			foreach ($contribution['publicationStatuses'] as $status)
			{
				if($status['current'] == 'true')
				{ 
					if($settings['groupByYear'])
					{
						$array['contributionToJournal'][$i]['year'] = $status['publicationDate']['year'];
					}
					if($settings['showPublicationType'])
					{
						$array['contributionToJournal'][$i]['publicationStatus']['value'] = $status['publicationStatus'][0]['value'];
						$array['contributionToJournal'][$i]['publicationStatus']['uri'] = $status['publicationStatus'][0]['uri'];
					}
				}
			}
			$array['contributionToJournal'][$i]['rendering'] = $contribution['rendering'][0]['value'];
			$array['contributionToJournal'][$i]['uuid'] = $contribution['uuid'];
			$i++;
		}
		return $array;
	}
	
	/**
	 * query for single publication
	 * @return string xml
	 */
	public function getSinglePublication($uuid)
	{
		$xml = '<?xml version="1.0"?>
			<researchOutputsQuery>
			<uuids>' . $uuid . '</uuids>';
			
		//set locale:
		$xml .= CommonUtilities::getLocale();
		
		//and everything else:
		$xml .= '<linkingStrategy>portalLinkingStrategy</linkingStrategy>
			<returnUsedContent>false</returnUsedContent>
			<navigationLink>true</navigationLink>
			</researchOutputsQuery>';
		$webservice = new WebService;
		$publication = $webservice->getJson('research-outputs', $xml);
		return $publication;
	}
	
	/**
	 * Complete available xml for POST query
	 * @return String xml
	 */
	public function getResearchOutputXml()
	{
		$xml = '<?xml version="1.0"?>
				<researchOutputsQuery>
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
					<idClassification>string</idClassification>
					<typeUri>string</typeUri>
					<publicationStatuses>string</publicationStatuses>
					<publicationCategory>string</publicationCategory>
					<peerReviewed>true</peerReviewed>
					<internationalPeerReviewed>true</internationalPeerReviewed>
					<publishedBeforeDate>1970-01-01T00:00:00.001Z</publishedBeforeDate>
					<publishedAfterDate>1970-01-01T00:00:00.001Z</publishedAfterDate>
					<workflowSteps>string</workflowSteps>
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
						<idClassification>string</idClassification>
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
						<idClassification>string</idClassification>
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
						<idClassification>string</idClassification>
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
							<idClassification>string</idClassification>
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
						<idClassification>string</idClassification>
						<organisationalUnitTypeUri>string</organisationalUnitTypeUri>
						<organisationalUnitPeriodStatus>ACTIVE</organisationalUnitPeriodStatus>
					</forOrganisationalUnits>
				</researchOutputsQuery>

		';
	}
}
?>
