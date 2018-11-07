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

class CurriculumVitae
{
	public function getSingleCvPerPerson($personUuid){
		$xml = '<?xml version="1.0"?>
				<curriculumVitaeQuery>
				<rendering>standard</rendering>';
		//set locale:
		$xml .= CommonUtilities::getLocale();		  
		$xml .= '<forPersons>
					<uuids>' . $personUuid . '</uuids>
				</forPersons>
				</curriculumVitaeQuery>';
		$webservice = new WebService;
		$cv = $webservice->getJson('curricula-vitae', $xml); 

		return $cv['items'][0];
	}
}
