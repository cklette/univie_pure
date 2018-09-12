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

class Projects
{

    /**
     * produce xml for the list query of projects
     * @return array $projects
     */
    public function getProjectsList($settings)
    {
        $xml = '<?xml version="1.0"?>
			<projectsQuery>
			<rendering>short</rendering>
			<ordering>-startDate</ordering>
			<linkingStrategy>portalLinkingStrategy</linkingStrategy>';


        // only show specific Results regarding there workflowSteps
        if ($settings['projectWFlowSteps_validated'] == 1) {
           $xml .= "<workflowStep>validated</workflowStep>";
        }

        //set ordering:
        $xml .= $this->getOrderingXml($settings['orderProjects']);

        //set filter:
        $xml .= $this->getFilterXml($settings['filterProjects']);

        //set locale:
        $xml .= CommonUtilities::getLocale();

        //set page size:
        $xml .= CommonUtilities::getPageSize($settings['pageSize']);

        //set offset:
        $xml .= CommonUtilities::getOffset($settings['pageSize']);

        //either for organisations or for persons, both must not be submitted:
        $xml .= CommonUtilities::getPersonsOrOrganisationsXml($settings);

        $xml .= '</projectsQuery>';

        $webservice = new WebService;
        $projects = $webservice->getJson('projects', $xml);
        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($xml);
        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($projects);
        return $projects;
    }

    /**
     * set the ordering
     * @return string xml
     */
    public function getOrderingXml($order)
    {
        if (!$order) {
            $order = '-startDate';
        }//default
        $xml = '<ordering>' . $order . '</ordering>';
        return $xml;
    }

    /**
     * set the filter
     * @return string xml
     */
    public function getFilterXml($filter)
    {
        if (!$filter) {
            return;
        }
        $xml = '<projectStatus>' . $filter . '</projectStatus>';
        return $xml;
    }

    /**
     * Complete available xml for POST query
     * @return String xml
     */
    public function getProjectsXml()
    {
        $xml = '<?xml version="1.0"?>
		<projectsQuery>
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
		  <projectStatus>NOT_STARTED</projectStatus>
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
		</projectsQuery>';
    }
}

?>
