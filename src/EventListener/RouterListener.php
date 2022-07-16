<?php

declare(strict_types=1);

namespace Blacktrs\WPBundle\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouterListener implements EventSubscriberInterface
{
    private readonly string $apiPrefix;

    private readonly RequestMatcherInterface|UrlMatcherInterface $matcher;

    private readonly RouteCollection $collection;

    /**
     * @param Container $container
     *
     * @throws \Exception
     */
    public function __construct(ContainerInterface $container, private readonly ?LoggerInterface $logger = null)
    {
        /** @var Router $router */
        $router = $container->get('router');

        $this->apiPrefix = $container->getParameter('app.restPrefix');
        $this->matcher = $router->getMatcher();
        $this->collection = $router->getRouteCollection();
    }

    public function onKernelRequest(RequestEvent $requestEvent): void
    {
        $request = $requestEvent->getRequest();

        if (str_starts_with($request->getPathInfo(), '/_')) {
            return;
        }

        if ($request->attributes->has('_controller')) {
            // routing is already done
            return;
        }

        if ($request->attributes->has('_rest_api')) {
            $this->dispatchRestApi($request);

            return;
        }

        if ($request->attributes->has('_admin_ajax')) {
            $this->handleAdminAjax($request);

            return;
        }

        // handle templates by default
        $this->handleRequest($request);
    }

    private function dispatchRestApi(Request $request): void
    {
        $prefix = rtrim('/'.$this->apiPrefix, '/');
        $this->collection->addPrefix($prefix);
        $this->handleRequest($request);
    }

    private function handleAdminAjax(Request $request): void
    {
        $name = (string) $request->query->get('action', '');
        $adminPath = '/wp-admin/admin-ajax.php';

        if (($route = $this->collection->get($name)) === null) {
            return;
        }

        $this->collection->remove(array_keys(array_diff_key($this->collection->all(), [$name => $route])));

        $request->attributes->set('_templates', [$adminPath]);
        $this->handleRequest($request);
    }

    private function handleRequest(Request $request): void
    {
        $pathInfo = $request->getPathInfo();

        $templates = $request->attributes->get('_templates');
        $path = $this->getValidTemplatePath($templates);

        if ($request->attributes->has('_rest_api')) {
            $pathInfo = substr($pathInfo, \strlen('/'.$this->apiPrefix));
        }

        // add attributes based on the request (routing)
        try {
            $parameters = $this->matcher->match($path ?: $pathInfo);

            $this->logger?->info('Matched route "{route}".', [
                'route' => $parameters['_route'] ?? 'n/a',
                'route_parameters' => $parameters,
                'request_uri' => $request->getUri(),
                'method' => $request->getMethod(),
            ]);

            $request->attributes->add($parameters);
            unset($parameters['_route'], $parameters['_controller']);
            $request->attributes->set('_route_params', $parameters);
        } catch (ResourceNotFoundException $e) {
            $message = sprintf(
                'No route found for "%s %s"',
                $request->getMethod(),
                $request->getUriForPath($request->getPathInfo())
            );

            if ($referer = $request->headers->get('referer')) {
                $message .= sprintf(' (from "%s")', $referer);
            }

            throw new NotFoundHttpException($message, $e);
        } catch (MethodNotAllowedException $e) {
            $message = sprintf(
                'No route found for "%s %s": Method Not Allowed (Allow: %s)',
                $request->getMethod(),
                $request->getUriForPath($request->getPathInfo()),
                implode(', ', $e->getAllowedMethods())
            );

            throw new MethodNotAllowedHttpException($e->getAllowedMethods(), $message, $e);
        }
    }

    /**
     * @param array<string>|null $paths
     */
    private function getValidTemplatePath(?array $paths): ?string
    {
        if (!$paths) {
            return null;
        }

        $paths = array_filter($paths, fn (string $path): bool => array_filter($this->collection->all(), fn (Route $route): bool => $route->getPath() === $path) !== []);

        $paths = array_values($paths);

        if (empty($paths)) {
            return null;
        }

        return $paths[0];
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 60]],
        ];
    }
}
