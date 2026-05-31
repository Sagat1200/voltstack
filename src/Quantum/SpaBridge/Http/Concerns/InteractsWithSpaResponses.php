<?php

declare(strict_types=1);

namespace Quantum\SpaBridge\Http\Concerns;

use Quantum\Http\Response;
use Quantum\SpaBridge\Contracts\SpaResponderInterface;
use Quantum\SpaBridge\SpaResponder;

trait InteractsWithSpaResponses
{
    protected function spa(string $component, array $props = [], array $meta = []): Response
    {
        return $this->spaResponder()->page($component, $props, $meta);
    }

    protected function spaAction(array $data = [], array $meta = [], int $status = 200, ?string $message = null): Response
    {
        return $this->spaResponder()->action($data, $meta, $status, $message);
    }

    protected function spaValidation(array $errors, array $meta = [], int $status = 422): Response
    {
        return $this->spaResponder()->validation($errors, $meta, $status);
    }

    protected function spaError(string $message, int $status = 500, array $meta = [], array $errors = []): Response
    {
        return $this->spaResponder()->error($message, $status, $meta, $errors);
    }

    protected function spaRedirect(string $to, int $status = 302, array $meta = [], bool $replace = true): Response
    {
        return $this->spaResponder()->redirect($to, $status, $meta, $replace);
    }

    protected function spaResponder(): SpaResponder
    {
        if (function_exists('app')) {
            /** @var SpaResponderInterface $responder */
            $responder = app(SpaResponderInterface::class);

            if ($responder instanceof SpaResponder) {
                return $responder;
            }
        }

        return new SpaResponder($this->responses);
    }
}
