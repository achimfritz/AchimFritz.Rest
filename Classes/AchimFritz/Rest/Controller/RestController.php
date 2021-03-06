<?php
namespace AchimFritz\Rest\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "AchimFritz.ServiceDescription".*
 *                                                                        *
 *                                                                        */

use Neos\Flow\Annotations as Flow;
use Neos\Error\Messages\Message;
use Neos\Flow\Mvc\Controller\ControllerContext;

/**
 * RestController
 */
class RestController extends \Neos\Flow\Mvc\Controller\RestController
{

    /**
     * Supported content types. Needed for HTTP content negotiation.
     * @var array
     */
    protected $supportedMediaTypes = array('text/html', 'application/json', 'application/xml');

    /**
     * @var string
     */
    protected $mediaType;

    /**
     * @var array
     */
    protected $viewFormatToObjectNameMap = array('json' => 'AchimFritz\\Rest\\Mvc\\View\\JsonView');

    /**
     * Initializes the controller
     *
     * This method should be called by the concrete processRequest() method.
     *
     * @param \Neos\Flow\Mvc\RequestInterface $request
     * @param \Neos\Flow\Mvc\ResponseInterface $response
     * @throws \Neos\Flow\Mvc\Exception\UnsupportedRequestTypeException
     */
    protected function initializeController(\Neos\Flow\Mvc\RequestInterface $request, \Neos\Flow\Mvc\ResponseInterface $response)
    {
        $this->parentInitializeController($request, $response);
        // override request.format with NegotiatedMediaType aka HTTP-Request Content-Type and set Content-Type to response
        $this->mediaType = $this->request->getHttpRequest()->getNegotiatedMediaType($this->supportedMediaTypes);
        if (in_array($this->mediaType, $this->supportedMediaTypes) === false) {
            $this->throwStatus(406);
        } else {
            $this->request->setFormat(preg_replace('/.*\/(.*)/', '$1', $this->mediaType));
            // sets the Content-Type to the response
            $this->response->setHeader('Content-Type', $this->mediaType . '; charset=UTF-8', true);
        }
    }

    /**
     * Determines the action method and assures that the method exists.
     *
     * @return string The action method name
     * @throws NoSuchActionException if the action specified in the request object does not exist (and if there's no default action either).
     */
    protected function resolveActionMethodName()
    {
        // sets correct $this->request actionMethodName:
        $actionMethodName = parent::resolveActionMethodName();
        // replace controllerContext sets which was set ini initializeController callec before actionMethod was replaced
        $this->controllerContext = new ControllerContext($this->request, $this->response, $this->arguments, $this->uriBuilder);
        return $actionMethodName;
    }


    /**
     * @param \Neos\Flow\Mvc\RequestInterface $request
     * @param \Neos\Flow\Mvc\ResponseInterface $response
     * @throws \Neos\Flow\Mvc\Exception\UnsupportedRequestTypeException
     * @return void
     */
    protected function parentInitializeController(\Neos\Flow\Mvc\RequestInterface $request, \Neos\Flow\Mvc\ResponseInterface $response)
    {
        parent::initializeController($request, $response);
    }


    /**
     * errorAction
     *
     * @return void
     */
    protected function errorAction()
    {
        // we like to have a 400 status
        $this->response->setStatus(400);
        if ($this->mediaType === 'application/json') {
            $this->addFlashMessage('FATAL', '', Message::SEVERITY_ERROR);
        } else {
            return parent::errorAction();
        }
    }

    /**
     * handleException
     *
     * @param \Exception $e
     * @return void
     */
    protected function handleException(\Exception $e)
    {
        $this->response->setStatus(500);
        // may be we want also an exceptionHandler e.g. to notify somebody, ...
        $this->addFlashMessage($e->getMessage(), get_class($e), Message::SEVERITY_ERROR, array(), $e->getCode());
    }

    /**
     * Redirects the request to another action and / or controller.
     *
     * @param string $actionName Name of the action to forward to
     * @param string $controllerName Unqualified object name of the controller to forward to. If not specified, the current controller is used.
     * @param string $packageKey Key of the package containing the controller to forward to. If not specified, the current package is assumed.
     * @param array $arguments Array of arguments for the target action
     * @param integer $delay (optional) The delay in seconds. Default is no delay.
     * @param integer $statusCode (optional) The HTTP status code for the redirect. Default is "303 See Other"
     * @param string $format The format to use for the redirect URI
     * @return void
     * @throws \Neos\Flow\Mvc\Exception\StopActionException
     * @see forward()
     * @api
     */
    protected function redirect($actionName, $controllerName = null, $packageKey = null, array $arguments = null, $delay = 0, $statusCode = 303, $format = null)
    {
        if ($this->mediaType === 'application/json') {
            // render all arguments
            if ($arguments !== null) {
                foreach ($arguments as $key => $value) {
                    $this->view->assign($key, $value);
                }
            }
            // get uri (like AbstractController->redirect())
            // do we need/want the uri?
            if ($packageKey !== null && strpos($packageKey, '\\') !== false) {
                list($packageKey, $subpackageKey) = explode('\\', $packageKey, 2);
            } else {
                $subpackageKey = null;
            }
            $this->uriBuilder->reset();
            if ($format === null) {
                $this->uriBuilder->setFormat($this->request->getFormat());
            } else {
                $this->uriBuilder->setFormat($format);
            }

            $uri = $this->uriBuilder->setCreateAbsoluteUri(true)->uriFor($actionName, $arguments, $controllerName, $packageKey, $subpackageKey);
            $this->view->assign('see', $uri);
        } else {
            return parent::redirect($actionName, $controllerName, $packageKey, $arguments, $delay, $statusCode, $format);
        }
    }
}
