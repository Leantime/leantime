<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Handles HTTP cache headers configured via the Cache attribute.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class CacheAttributeListener implements EventSubscriberInterface
{
    /**
     * @var \SplObjectStorage<Request, \DateTimeInterface>
     */
    private \SplObjectStorage $lastModified;

    /**
     * @var \SplObjectStorage<Request, string>
     */
    private \SplObjectStorage $etags;

    public function __construct(
        private ?ExpressionLanguage $expressionLanguage = null,
    ) {
        $this->lastModified = new \SplObjectStorage();
        $this->etags = new \SplObjectStorage();
    }

    /**
     * Handles HTTP validation headers.
     *
     * @return void
     */
    public function onKernelControllerArguments(ControllerArgumentsEvent $event)
    {
        $request = $event->getRequest();

        if (!\is_array($attributes = $request->attributes->get('_cache') ?? $event->getAttributes()[Cache::class] ?? null)) {
            return;
        }

        $request->attributes->set('_cache', $attributes);
        $response = null;
        $lastModified = null;
        $etag = null;

        /** @var Cache[] $attributes */
        foreach ($attributes as $cache) {
            if (null !== $cache->lastModified) {
                $lastModified = $this->getExpressionLanguage()->evaluate($cache->lastModified, array_merge($request->attributes->all(), $event->getNamedArguments()));
                ($response ??= new Response())->setLastModified($lastModified);
            }

            if (null !== $cache->etag) {
                $etag = hash('sha256', $this->getExpressionLanguage()->evaluate($cache->etag, array_merge($request->attributes->all(), $event->getNamedArguments())));
                ($response ??= new Response())->setEtag($etag);
            }
        }

        if ($response?->isNotModified($request)) {
            $event->setController(static fn () => $response);
            $event->stopPropagation();

            return;
        }

        if (null !== $etag) {
            $this->etags[$request] = $etag;
        }
        if (null !== $lastModified) {
            $this->lastModified[$request] = $lastModified;
        }
    }

    /**
     * Modifies the response to apply HTTP cache headers when needed.
     *
     * @return void
     */
    public function onKernelResponse(ResponseEvent $event)
    {
        $request = $event->getRequest();

        /** @var Cache[] $attributes */
        if (!\is_array($attributes = $request->attributes->get('_cache'))) {
            return;
        }
        $response = $event->getResponse();

        // http://tools.ietf.org/html/draft-ietf-httpbis-p4-conditional-12#section-3.1
        if (!\in_array($response->getStatusCode(), [200, 203, 300, 301, 302, 304, 404, 410])) {
            unset($this->lastModified[$request]);
            unset($this->etags[$request]);

            return;
        }

        if (isset($this->lastModified[$request]) && !$response->headers->has('Last-Modified')) {
            $response->setLastModified($this->lastModified[$request]);
        }

        if (isset($this->etags[$request]) && !$response->headers->has('Etag')) {
            $response->setEtag($this->etags[$request]);
        }

        unset($this->lastModified[$request]);
        unset($this->etags[$request]);
        $hasVary = $response->headers->has('Vary');

        foreach (array_reverse($attributes) as $cache) {
            if (null !== $cache->smaxage && !$response->headers->hasCacheControlDirective('s-maxage')) {
                $response->setSharedMaxAge($this->toSeconds($cache->smaxage));
            }

            if ($cache->mustRevalidate) {
                $response->headers->addCacheControlDirective('must-revalidate');
            }

            if (null !== $cache->maxage && !$response->headers->hasCacheControlDirective('max-age')) {
                $response->setMaxAge($this->toSeconds($cache->maxage));
            }

            if (null !== $cache->maxStale && !$response->headers->hasCacheControlDirective('max-stale')) {
                $response->headers->addCacheControlDirective('max-stale', $this->toSeconds($cache->maxStale));
            }

            if (null !== $cache->staleWhileRevalidate && !$response->headers->hasCacheControlDirective('stale-while-revalidate')) {
                $response->headers->addCacheControlDirective('stale-while-revalidate', $this->toSeconds($cache->staleWhileRevalidate));
            }

            if (null !== $cache->staleIfError && !$response->headers->hasCacheControlDirective('stale-if-error')) {
                $response->headers->addCacheControlDirective('stale-if-error', $this->toSeconds($cache->staleIfError));
            }

            if (null !== $cache->expires && !$response->headers->has('Expires')) {
                $response->setExpires(new \DateTimeImmutable('@'.strtotime($cache->expires, time())));
            }

            if (!$hasVary && $cache->vary) {
                $response->setVary($cache->vary, false);
            }
        }

        foreach ($attributes as $cache) {
            if (true === $cache->public) {
                $response->setPublic();
            }

            if (false === $cache->public) {
                $response->setPrivate();
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => ['onKernelControllerArguments', 10],
            KernelEvents::RESPONSE => ['onKernelResponse', -10],
        ];
    }

    private function getExpressionLanguage(): ExpressionLanguage
    {
        return $this->expressionLanguage ??= class_exists(ExpressionLanguage::class)
            ? new ExpressionLanguage()
            : throw new \LogicException('Unable to use expressions as the Symfony ExpressionLanguage component is not installed. Try running "composer require symfony/expression-language".');
    }

    private function toSeconds(int|string $time): int
    {
        if (!is_numeric($time)) {
            $now = time();
            $time = strtotime($time, $now) - $now;
        }

        return $time;
    }
}
