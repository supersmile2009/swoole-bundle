<?php

declare(strict_types=1);

namespace K911\Swoole\Bridge\Symfony\HttpFoundation;

use Swoole\Http\Response as SwooleResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ResponseProcessor implements ResponseProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(HttpFoundationResponse $httpFoundationResponse, SwooleResponse $swooleResponse): void
    {
        foreach ($httpFoundationResponse->headers->allPreserveCaseWithoutCookies() as $name => $values) {
            $swooleResponse->header($name, \implode(', ', $values));
        }

        foreach ($httpFoundationResponse->headers->getCookies() as $cookie) {
            $swooleResponse->cookie(
                $cookie->getName(),
                $cookie->getValue() ?? '',
                $cookie->getExpiresTime(),
                $cookie->getPath(),
                $cookie->getDomain() ?? '',
                $cookie->isSecure(),
                $cookie->isHttpOnly(),
                $cookie->getSameSite() ?? ''
            );
        }

        $swooleResponse->status($httpFoundationResponse->getStatusCode());

        if ($httpFoundationResponse instanceof BinaryFileResponse) {
            $swooleResponse->sendfile($httpFoundationResponse->getFile()->getRealPath());
        } elseif ($httpFoundationResponse instanceof StreamedResponse) {
            \ob_start(function (string $payload) use ($swooleResponse) {
                if ('' !== $payload) {
                    $swooleResponse->write($payload);
                }

                return '';
            }, 8192);
            $httpFoundationResponse->sendContent();
            \ob_end_clean();
            $swooleResponse->end();
        } else {
            $swooleResponse->end($httpFoundationResponse->getContent());
        }
    }
}
