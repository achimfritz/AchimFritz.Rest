<?php
namespace AchimFritz\Rest\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AchimFritz.ServiceDescription".*
 *                                                                        *
 *                                                                        */

use Neos\Flow\Annotations as Flow;

class ExampleController extends RestController {

	/**
	 * @return void
	 */
	public function listAction() {
		$this->view->assign('content', 'foo content');
		$this->view->assign('title', 'foo title');
		$this->addFlashMessage('notice flash message: this is your Content-Type ' . $this->mediaType, 'flashMessage title', \Neos\Flow\Error\Message::SEVERITY_NOTICE);
		$this->addFlashMessage('error flash message', 'second flashMessage title', \Neos\Flow\Error\Message::SEVERITY_ERROR);
	}

}

?>
