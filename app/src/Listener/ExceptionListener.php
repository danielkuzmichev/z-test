<?php

namespace App\Listener;

use App\Exception\DomainException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsEventListener(event: 'kernel.exception', method: 'onKernelException')]
class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $response = $this->createResponseFromException($exception);
        $event->setResponse($response);
    }

    private function createResponseFromException(\Throwable $exception): JsonResponse
    {
        if ($exception instanceof DomainException) {
            return $this->createDomainErrorResponse($exception);
        }

        if (
            $exception instanceof HttpException
            && ($prev = $exception->getPrevious()) instanceof ValidationFailedException
        ) {
            return $this->createValidationErrorResponse($prev);
        }

        return $this->createGenericErrorResponse($exception);
    }

    private function createDomainErrorResponse(DomainException $exception): JsonResponse
    {
        return new JsonResponse([
            'error' => 'Domain error',
            'message' => $exception->getMessage(),
        ], $exception->getCode() ?: 400);
    }

    private function createValidationErrorResponse(ValidationFailedException $exception): JsonResponse
    {
        $violations = $exception->getViolations();
        $errors = [];

        /** @var ConstraintViolationInterface $violation */
        foreach ($violations as $violation) {
            $errors[$violation->getPropertyPath()][] = $violation->getMessage();
        }

        return new JsonResponse([
            'error' => 'Validation failed',
            'violations' => $errors,
        ], 400);
    }

    private function createGenericErrorResponse(\Throwable $exception): JsonResponse
    {
        $statusCode = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : 500;

        return new JsonResponse([
            'error' => 'Internal Server Error',
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ], $statusCode);
    }
}
