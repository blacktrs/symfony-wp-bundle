<?php

namespace Blacktrs\WPBundle\Kernel;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;

abstract class WPKernel extends Kernel
{
    private const TEMPLATE_HIERARCHY = [
        'index',
        '404',
        'archive',
        'author',
        'category',
        'tag',
        'taxonomy',
        'date',
        'home',
        'frontpage',
        'page',
        'paged',
        'search',
        'single',
        'singular',
        'attachment',
        'embed',
    ];

    private static self $instance;

    public static function setInstance(self $kernel): void
    {
        self::$instance = $kernel;
    }

    public static function getInstance(): self
    {
        return self::$instance;
    }

    public function dispatch(): void
    {
        $path = (string)parse_url($_SERVER['REQUEST_URI'] ?? '', \PHP_URL_PATH);

        // Disable redirects for internal symfony routes
        if (str_starts_with($path, '/_')) {
            remove_action('template_redirect', 'redirect_canonical');
        }

        $isWpAdmin = str_starts_with($path, '/wp/wp-admin/') || str_starts_with($path, '/wp/wp-includes/');

        if ($isWpAdmin) {
            return;
        }

        foreach (self::TEMPLATE_HIERARCHY as $type) {
            add_filter("{$type}_template", $this->resolveTemplateRequest(...), 1000, 3);
        }

        add_action('rest_api_init', $this->resolveRestRequest(...), PHP_INT_MAX - 1000);
    }

    /**
     * @param array<string> $templates
     */
    private function resolveTemplateRequest(string $template, string $type, array $templates): string
    {
        $request = Request::createFromGlobals();

        $templates = array_map(fn (string $template): string => '/' . $template, (array)$templates);

        $request->attributes->set('_templates', $templates);

        $this->handleHttpKernel($request);

        return $template;
    }

    private function resolveRestRequest(): void
    {
        $request = Request::createFromGlobals();
        $path = substr($request->getRequestUri(), strlen(rest_get_url_prefix()) + 1);

        foreach (array_keys(rest_get_server()->get_routes()) as $route) {
            preg_match('@^' . $route . '$@i', $path, $matches);

            if (!empty($matches)) {
                return;
            }
        }

        $request->attributes->set('_rest_api', true);
        $this->handleHttpKernel($request);
    }

    private function handleHttpKernel(Request $request): void
    {
        $response = $this->handle($request);
        $response->send();

        $this->terminate($request, $response);
    }

    public function registerHooks(): void
    {
        // override method to register handlers for actions and filters
    }
}
