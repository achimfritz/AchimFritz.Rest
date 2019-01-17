<?php
namespace AchimFritz\Rest\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Kickstart".       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Neos\Flow\Annotations as Flow;

/**
 * Service for the Kickstart generator
 *
 */
class GeneratorService extends \TYPO3\Kickstart\Service\GeneratorService {

	/**
	 * Render the given template file with the given variables
	 *
	 * @param string $templatePathAndFilename
	 * @param array $contextVariables
	 * @return string
	 * @throws \TYPO3\Fluid\Core\Exception
	 */
	protected function renderTemplate($templatePathAndFilename, array $contextVariables) {
		$name = $templatePathAndFilename;
		if (strpos($name, 'resource://TYPO3.Kickstart/Private/Generator/View') !== FALSE
			|| $name === 'resource://TYPO3.Kickstart/Private/Generator/Controller/CrudControllerTemplate.php.tmpl'
		) {
			$name = str_replace('TYPO3.Kickstart', 'AchimFritz.Rest', $name);
		}
		return parent::renderTemplate($name, $contextVariables);
	}

	/**
	 * @param string $packageKey The package key of the controller's package
	 * @param boolean $overwrite Overwrite any existing files?
	 * @return array An array of generated filenames
	 */
	public function generateException($packageKey, $overwrite = FALSE) {
		$templatePathAndFilename = 'resource://AchimFritz.Rest/Private/Generator/ExceptionTemplate.php.tmpl';

		$contextVariables = array();
		$contextVariables['packageNamespace'] = str_replace('.', '\\', $packageKey);

		$fileContent = $this->renderTemplate($templatePathAndFilename, $contextVariables);

		$classPath = $this->packageManager->getPackage($packageKey)->getClassesNamespaceEntryPath();
		$path = $classPath . 'Exception.php';
		$targetPathAndFilename = $path;

		$this->generateFile($targetPathAndFilename, $fileContent, $overwrite);

		return $this->generatedFiles;
	}

}
