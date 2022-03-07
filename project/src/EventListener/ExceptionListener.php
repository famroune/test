<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use App\Http\ApiResponse;
use Exception;

/**
 * gestion des évènements d'exception
 */
class ExceptionListener
{
    /**
     * @param ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent  $event)
    {
        if($event->getThrowable() instanceof Exception) {
            $exception = $event->getThrowable();
            $response = $this->createApiResponse($exception);
            $event->setResponse($response);
        } else {
            $error = $event->getThrowable();
            $response = new ApiResponse($error->getMessage(), ["code" => 1000], [], 401);
            $event->setResponse($response);
        }
    }
    
    /**
     * Creates the ApiResponse from any Exception
     *
     * @param \Exception $exception
     *
     * @return ApiResponse
     */
    private function createApiResponse(\Exception $exception)
    {
        $datas = null;
        $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 401;
        $errors     = [];
        
        $message = $exception->getMessage();
        
        return new ApiResponse($message, $datas, $errors, $statusCode);
    }
}