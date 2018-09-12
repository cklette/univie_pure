<?php

namespace Univie\UniviePure\Controller;

//use Univie\UniviePure\Service\WebService;
//use TYPO3\CMS\Core\Utility\GeneralUtility;
use Univie\UniviePure\Endpoints\ResearchOutput;
use Univie\UniviePure\Endpoints\Activities;
use Univie\UniviePure\Endpoints\PressMedia;
use Univie\UniviePure\Endpoints\Projects;

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

/**
 * PublicationController
 */
class PureController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     * @inject
     */
    protected $configurationManager;

    /**
     * @var array
     */
    protected $settings = array();

    /**
     * Get settings from ConfigurationManager ziehen
     */
    public function initialize()
    {
        $settings = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS
        );
        $this->settings = $settings;
    }

    /**
     * action list
     *
     * @return void
     */
    public function listAction()
    {
        //&tx_univiepure_univiepure[filter]=qwer
        //reduce the list:
        if ($this->request->hasArgument('filter')) {
            $this->settings['filter'] = $this->request->getArgument('filter');
            $this->view->assign('filter', $this->request->getArgument('filter'));
        }

        switch ($this->settings['what_to_display']) {
            case 'PUBLICATIONS':
                $pub = new ResearchOutput;
                $view = $pub->getPublicationList($this->settings);
                $this->view->assign('publications', $view);
                break;
            case 'ACTIVITIES':
                $act = new Activities;
                $view = $act->getActivitiesList($this->settings);
                $this->view->assign('activities', $view);
                break;
            case 'PRESS-MEDIA':
                $pressMedia = new PressMedia;
                $view = $pressMedia->getPressMediaList($this->settings);
                $this->view->assign('pressMedia', $view);
                break;
            case 'PROJECTS':
                $projects = new Projects;
                $view = $projects->getProjectsList($this->settings);
                $this->view->assign('projects', $view);
                break;
            case 'DETAIL':
                //Should never occur
                break;
        }
    }


    /**
     * action show
     *
     * @param \Univie\UniviePure\Domain\Model\Publication $publication
     * @return void
     */
    public function showAction()
    {
        $arguments = $this->request->getArguments();

        switch ($arguments['what2show']) {
            case 'publ':
                $pub = new ResearchOutput;
                $view = $pub->getSinglePublication($arguments['uuid']);
                $this->view->assign('publication', $view);
                break;
            case 'act':
                //Should never occur. Is linked to portal
                $act = new Activities;
                $view = $pub->getSingleActivity($arguments['uuid']);
                $this->view->assign('activity', $view);
                break;
            case 'PRESS-MEDIA':
                //Should never occur. Is linked to portal
                $clip = new PressMedia;
                break;
            case 'PROJECT':
                //Should never occur. Is linked to portal
                $pro = new Project;
                break;
        }
    }

}
