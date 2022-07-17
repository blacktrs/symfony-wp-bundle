<?php

if (defined('ABSPATH')) {
    return;
}

if (!function_exists('remove_action')) {
    function remove_action(mixed ...$args): void
    {
    }
}

if (!function_exists('add_filter')) {
    function add_filter(mixed ...$args): void
    {
    }
}

if (!function_exists('add_action')) {
    function add_action(mixed ...$args): void
    {
    }
}

if (!function_exists('rest_get_url_prefix')) {
    function rest_get_url_prefix(mixed ...$args): string
    {
        return '';
    }
}

if (!function_exists('rest_get_server')) {
    function rest_get_server(mixed ...$args): object
    {
        return new stdClass();
    }
}