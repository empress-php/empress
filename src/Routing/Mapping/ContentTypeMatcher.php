<?php

declare(strict_types=1);

namespace Empress\Routing\Mapping;

use Amp\Http\Server\Request;

final class ContentTypeMatcher
{
    public function match(ContentTypeAwareHandlerInterface $handler, Request $request): bool
    {
        if ($handler->getContentType() === null) {
            return true;
        }

        $acceptHeader = $request->getHeader('Accept');

        return $handler->getContentType() === $acceptHeader;
    }
}
