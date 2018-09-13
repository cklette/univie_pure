<?php
 
namespace Univie\UniviePure\ViewHelpers;

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
 
class PaginateViewHelper extends \TYPO3\CMS\Fluid\Core\Widget\AbstractWidgetViewHelper 
{
 
    /**
     * @var \Univie\UniviePure\Controller\PaginateController 
     * @inject
     */
    protected $controller;
 
    /**
     * Injection of widget controller
     * 
     * @param \Univie\UniviePure\Controller\PaginateController $controller
     * @return void
     */
    public function injectController(\Univie\UniviePure\Controller\PaginateController $controller) 
    {
        $this->controller = $controller; 
    }
 
    /**
     * The render method of widget
     *
     * @param mixed $objects \TYPO3\CMS\ExtBase\Persistence\QueryResultInterface,
     *        \TYPO3\CMS\ExtBase\Persistence\ObjectStorage object or array
     * @param string $as
     * @param array $configuration
     * @return string
     */
    public function render($objects, $as, array $configuration = array('itemsPerPage' => 10, 'insertAbove' => FALSE, 'insertBelow' => TRUE)) 
    {
        return $this->initiateSubRequest();
    }
}
?>