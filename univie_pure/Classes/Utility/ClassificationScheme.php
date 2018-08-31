<?php
namespace Univie\UniviePure\Utility;
use Univie\UniviePure\Utility\CommonUtilities;
use Univie\UniviePure\Service\WebService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
 * ClassificationScheme and structural queries
 *
 *
 * /ws/rest/classificationschemehierarchy?baseUri=/dk/atira/pure/organisation/organisationtypes
 * /ws/rest/classificationschemehierarchy?baseUri=/dk/atira/pure/researchoutput/researchoutputtypes
 * /ws/rest/classificationschemehierarchy?baseUri=/dk/atira/pure/activity/activitytypes
 * /ws/rest/classificationschemehierarchy?baseUri=/dk/atira/pure/person/employmenttypes
 *
 */
class ClassificationScheme
{

	const RESEARCHOUTPUT = '/dk/atira/pure/researchoutput/researchoutputtypes';

	const ACTIVITIES = '/dk/atira/pure/activity/activitytypes';

	const PRESSMEDIA = '/dk/atira/pure/clipping/clippingtypes';

	const PROJECTS = '/dk/atira/pure/upm/fundingprogramme';

	/**
	 * @var $lang String
	 */
	protected $locale = '';

	/**
	 * set common stuff
	 */
	public function __construct()
	{
		//Set the backend language:
		$this->locale = CommonUtilities::getBackendLanguage();
	}

	/**
	 * getter for locale
	 * @return String locale, frontend language
	 */
	private function getLocale()
	{
		return $this->locale;
	}

	/*
	 * Ajax call for backend choosing persons:
	 * @param \TYPO3\CMS\Backend\Form\FormEngine $tceForms Reference to an TCEforms instance
	 */
	public function ajaxGetPerson($PA, $fObj){

		$query = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('query');

		if($query)
		{
			$items = array();
			$xml ='<personsQuery>
						<searchString>' . $query . '</searchString>
						<locale>de_DE</locale>
						<size>100</size>
						<fields>name.*</fields>
						<fields>uuid</fields>
					</personsQuery>';
			$webservice = new WebService;
			$persons = $webservice->getJson('persons',$xml);

			foreach($persons['items'] as $pers)
			{
				$item = array($pers['name']['lastName'] . ', ' . $pers['name']['firstName'],$pers['uuid']);
				array_push($items,$item);
			}
		}

        // Put the wizard into $output and return it
        $output = '<style>.typo3-TCEforms-suggest-resultlist li:hover{background-color:#ffb;}</style>
			<div style="margin-top: 8px; margin-left: 4px;">
			<input type="text" name="query" id="query" placeholder="' 
				. \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('suggest-placeholder','univie_pure') . 
			'" autocomplete="off">'; 
        
        if(isset($items))
        {
			$output .= '<div class="typo3-TCEforms-suggest-choices" id="typo3-ucris-persons-suggest">
			<ul class="typo3-TCEforms-suggest-resultlist">';
			foreach($items as $item)
			{
				$output .= '<li onclick="addToList(this.id)" id="' . $item[1] . '">
						<span class="suggest-label"><span title="' . $item[0] . '">' . $item[0] . '</span></span><br><span class="suggest-uid">' . $item[1] . '</span>
					</li>';
			}
			$output .= '</ul></div>';
		}

        $output .= '</div>
			<script>
			function addToList(id){
				var x = document.getElementById(id);
				var nodeList = x.childNodes;
				var opt = document.createElement("option");
				opt.value = id;
				opt.text = nodeList[1].innerText;
				opt.setAttribute("selected", true);
				opt.setAttribute("id", id);
				var sel = document.getElementsByName("' . $PA['itemName'] . '");
				for(var i=0; i < sel[0].options.length; i++){
					optCheck = sel[0].options[i];
					if(optCheck.value == id){
						alert(opt.text + "' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('suggest-already-in-list','univie_pure') . '");
						closeDiv();
						return;
					}
				}
				sel[0].appendChild(opt);
				closeDiv();
			}
			function closeDiv(){
				var parentDiv = document.getElementById("typo3-ucris-persons-suggest");
				parentDiv.setAttribute("style", "display:none");
			}
			</script>';
		return $output;
	}


	/**
	 *
	 * Organisation from which publications should be displayed
	 */
	public function getOrganisations(&$config)
	{
		$items = array();
		$postData = '<?xml version="1.0"?>
						<organisationalUnitsQuery>
						  <locale>' . $this->getLocale() . '</locale>
						  <ordering>name</ordering>
						  <size>300</size>
						  <fields>uuid</fields>
						  <fields>name.value</fields>
						  <returnUsedContent>true</returnUsedContent>
						  <organisationalUnitPeriodStatus>ACTIVE</organisationalUnitPeriodStatus>
						</organisationalUnitsQuery>
						';
		$webservice = new WebService;
		$organisations = $webservice->getJson('organisational-units',$postData);
		if(is_array($organisations))
		{
			foreach($organisations['items'] as $org)
			{
				$item = array($org['name']['0']['value'],$org['uuid']);
				array_push($config['items'],$item);
			}
		}
	}

	/*
	 * Persons list for select user func:
	 */
	public function getPersons(&$config){
		$items = array();
		$personsList = $config['row']['settings.selectorPersons'];
		if($personsList != '')
		{
			$persons = explode(',',$personsList);
		}

		$personXML = '<?xml version="1.0"?>
				<personsQuery>
					<fields>uuid</fields>
					<fields>name.*</fields>
					<ordering>lastName</ordering>
					<size>20000</size>
					<employmentStatus>ACTIVE</employmentStatus>';
		if(count($persons) > 0)
		{
			foreach((array) $persons as $person)
			{
				if(strpos($person, "|"))
				{
					$tmp = explode("|", $person);
					$person = $tmp[0];
				}
				$personXML .= '<uuids>'. $person . '</uuids>';
			}

			$personXML .= '</personsQuery>';
			$webservice = new WebService;
			$persons = $webservice->getJson('persons',$personXML);
			foreach($persons['items'] as $pers)
			{
				$item = array($pers['name']['lastName'] . ', ' . $pers['name']['firstName'],$pers['uuid']);
				array_push($config['items'],$item);
			}
		}
	}

	/**
	 * structural query for publication types
	 * @return String xml
	 */
	public function getTypesFromPublications(&$config)
	{
		/*<?xml version="1.0"?>
		<classificationSchemesQuery>
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
		  <baseUri>string</baseUri>
		  <containedClassificationUri>string</containedClassificationUri>
		</classificationSchemesQuery>
		*/
		$items = array();
		$classificationXML = '<?xml version="1.0"?>
					<classificationSchemesQuery>
					  <size>300</size>
					  <locale>' . $this->getLocale() . '</locale>
					  <returnUsedContent>true</returnUsedContent>
					  <navigationLink>true</navigationLink>
					  <baseUri>' . self::RESEARCHOUTPUT . '</baseUri>
					</classificationSchemesQuery>
					';
		$webservice = new WebService;
		$publicationTypes = $webservice->getJson('classification-schemes',$classificationXML);
		//api/511/:
		$sorted = $this->sortClassification($publicationTypes);
		$this->sorted2items($sorted,$config);
	}

	/**
	 * sort hierarchical
	 */
	public function sorted2items($sorted,&$config)
	{
		foreach($sorted as $optGroup)
		{
			$label = '----- ' . $optGroup['title'] . ': -----';
			$item = array($label,'--div--');
			array_push($config['items'],$item);
			foreach($optGroup['child'] as $opt)
			{
				$item = array($opt['title'],$opt['uri']);
				array_push($config['items'],$item);
			}
		}

	}


	/**
	 * structural query for activity types
	 * @return String xml
	 */
	public function getTypesFromActivities(&$config)
	{
		$items = array();
		$classificationXML = '<?xml version="1.0"?>
					<classificationSchemesQuery>
					  <size>300</size>
					  <locale>' . $this->getLocale() . '</locale>
					  <returnUsedContent>true</returnUsedContent>
					  <navigationLink>true</navigationLink>
					  <baseUri>' . self::ACTIVITIES . '</baseUri>
					</classificationSchemesQuery>
					';
		$webservice = new WebService;
		$activityTypes = $webservice->getJson('classification-schemes',$classificationXML);
		$sorted = $this->sortClassification($activityTypes);
		$this->sorted2items($sorted,$config);
	}

	/**
	 * Sort classifications to hierarchical tree
	 * first in api/511
	 * @return array hierarchicalTree
	 */
	public function sortClassification($unsorted)
	{
		$sorted = array();
		$i = 0;
		foreach($unsorted['items'][0]['containedClassifications'] as $parent)
		{
			if(($parent['disabled'] != 1) && ($this->classificationHasChild($parent)))
			{
				$sorted[$i]['uri'] = $parent['uri'];
				$sorted[$i]['title'] = $parent['terms'][0]['value'];
				$j=0;
				foreach($parent['classificationRelations'] as $child)
				{
					if($child['relationType'][0]['uri'] == '/dk/atira/pure/core/hierarchies/child')
					{
						if(!$this->isChildEnabledOnRootLevel($unsorted, $child['relatedTo'][0]['uri']))
						{
							$c = array($child['relatedTo'][0]['uri'] => $child['relatedTo'][0]['value']); 
							$sorted[$i]['child'][$j]['uri'] = $child['relatedTo'][0]['uri'];
							$sorted[$i]['child'][$j]['title'] = $child['relatedTo'][0]['value'];
							$j++;
						}
					}
				}
				$i++;

			}
		}
		return $sorted;
	}
	
	/*
	 * Check for children
	 */
	public function classificationHasChild($parent)
	{
		$has = FALSE;
		if(array_key_exists('classificationRelations', $parent))
		{
		foreach($parent['classificationRelations'] as $child)
			{
				if($child['relationType'][0]['uri'] == '/dk/atira/pure/core/hierarchies/child')
				{
					if($child['relatedTo'][0]['value'] != '<placeholder>')
					{
						$has = TRUE;
						break;
					}
				}
			}
		}
		return $has;
	}
	
	/*
	 * Child is just a pointer to entry in root level. If disabled it is only visible on the root level:
	 */
	public function isChildEnabledOnRootLevel($roots, $childUri)
	{
		foreach($roots['items'][0]['containedClassifications'] as $root)
		{
			if($root['uri'] == $childUri) return $root['disabled'];
		}
	}
	
	/**
	 * structural query for press-media types
	 * @return String xml
	 */
	public function getTypesFromPressMedia(&$config)
	{
		$items = array();
		$classificationXML = '<?xml version="1.0"?>
					<classificationSchemesQuery>
					  <size>300</size>
					  <locale>' . $this->getLocale() . '</locale>
					  <returnUsedContent>true</returnUsedContent>
					  <navigationLink>true</navigationLink>
					  <baseUri>' . self::PRESSMEDIA . '</baseUri>
					</classificationSchemesQuery>
					';

		$webservice = new WebService;
		$activityTypes = $webservice->getJson('classification-schemes',$classificationXML);
		foreach($activityTypes['items']['0']['containedClassifications'] as $type)
		{
			$item = array($type['value'],$type['uri']);
			array_push($config['items'],$item);
		}
	}

	/**
	 * structural query for project types
	 * @return String xml
	 */
	public function getTypesFromProjects(&$config)
	{
		$items = array();
		$classificationXML = '<?xml version="1.0"?>
					<classificationSchemesQuery>
					  <size>300</size>
					  <locale>' . $this->getLocale() . '</locale>
					  <returnUsedContent>true</returnUsedContent>
					  <navigationLink>true</navigationLink>
					  <baseUri>' . self::PROJECTS . '</baseUri>
					</classificationSchemesQuery>
					';

		$webservice = new WebService;
		$projectsTypes = $webservice->getJson('classification-schemes',$classificationXML);
		foreach($projectsTypes['items']['0']['containedClassifications'] as $type)
		{
			$item = array($type['value'],$type['uri']);
			array_push($config['items'],$item);
		}
	}

	/**
	 * get uuid for email
	 * @param $email
	 * @return String uuid
	 */
	 public function getUuidForEmail($email)
	 {
		 $uuid = '123456789';//return some nonsens
		 $xml = '<?xml version="1.0"?>
				<personsQuery>
				  <searchString>' . $email . '</searchString>
				  <locale>' . $this->getLocale() . '</locale>
				  <fields>name</fields>
				</personsQuery>';
		$webservice = new WebService;
		$uuids = $webservice->getXml('persons',$xml);
		if($uuids['count'] == 1)
		{
			$uuid = $uuids['person']['@attributes']['uuid'];
		}
		return $uuid;
	 }

	/**
	 * Complete available xml for POST query
	 * @return String xml
	 */
	public function getClassificationResearchOutputXml()
	{
		$xml = '<?xml version="1.0"?>
			<classificationSchemesQuery>
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
			  <baseUri>string</baseUri>
			  <containedClassificationUri>string</containedClassificationUri>
			</classificationSchemesQuery>';
	}
}
?>
