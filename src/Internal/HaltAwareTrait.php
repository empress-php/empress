<?php

namespace Empress\Internal;

use Amp\Promise;
use Empress\Exception\HaltException;
use function Amp\call;

trait HaltAwareTrait
{
    private function resolveInjectionResult(ContextInjector $injector, callable $onProceed = null): Promise
    {
        return call(function () use ($injector, $onProceed) {
            try {
                $response = yield $injector->inject();
            } catch (HaltException $e) {
                $response = $e->toResponse();

                return $response;
            }

            if (\is_callable($onProceed)) {
                return yield $onProceed();
            }

            return $response;
        });
    }
}
