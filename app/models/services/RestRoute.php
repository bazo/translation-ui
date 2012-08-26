<?php
namespace Routes;
use Nette\Application\Routers\Route;
/**
 * RestRoute
 *
 * @author Martin
 */
class RestRoute extends Route
{
    const METHOD_POST = 4;
    const METHOD_GET = 8;
    const METHOD_PUT = 16;
    const METHOD_DELETE = 32;
    const RESTFUL = 64;

    public function match(\Nette\Http\IRequest $httpRequest)
    {
        $httpMethod = $httpRequest->getMethod();
		
        if (($this->flags & self::RESTFUL) == self::RESTFUL) {
            $presenterRequest = parent::match($httpRequest);
            if ($presenterRequest != NULL) {
				$payload = null;
				$payloadParameters = array();
                switch ($httpMethod) {
                    case 'GET':
                        $action = 'default';
                        break;
                    case 'POST':
                        $action = 'create';
                        break;
                    case 'PUT':
                        $action = 'update';
						$payload = file_get_contents("php://input");
						parse_str($payload, $payloadParameters);
                        break;
                    case 'DELETE':
                        $action = 'delete';
                        break;
                    default:
                        $action = 'default';
                }

                $parameters = $presenterRequest->getParameters();
                $parameters['action'] = $action;
				$parameters['payload'] = $payload;
				$parameters = array_merge($parameters, $payloadParameters);
                $presenterRequest->setParameters($parameters);
                return $presenterRequest;
            } else {
                return NULL;
            }
        }

        if (($this->flags & self::METHOD_POST) == self::METHOD_POST
            && $httpMethod != 'POST') {
                return NULL;
        }

        if (($this->flags & self::METHOD_GET) == self::METHOD_GET
            && $httpMethod != 'GET') {
                return NULL;
        }

        if (($this->flags & self::METHOD_PUT) == self::METHOD_PUT
            && $httpMethod != 'PUT') {
                return NULL;
        }

        if (($this->flags & self::METHOD_DELETE) == self::METHOD_DELETE
            && $httpMethod != 'DELETE') {
                return NULL;
        }

        return parent::match($httpRequest);
    }
}
