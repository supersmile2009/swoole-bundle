<?php

declare(strict_types=1);

namespace K911\Swoole\Tests\Fixtures\Symfony\TestBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

final class StreamedResponseController
{
    /**
     * @Route(methods={"GET"}, path="/stream/string")
     */
    public function sendStreamFromRequest(Request $request): StreamedResponse
    {
        return new StreamedResponse(function () use ($request): void {
            echo \sprintf('Full URI: %s', $request->getUri());
        });
    }
}
