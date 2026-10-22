<?php

namespace App\Services;

class NotificationDestination
{
    public static function resolve(string $url): string
    {
        $parts = parse_url($url);
        if ($parts === false || preg_match('/[\x00-\x20\\\\]/', $url) || str_starts_with($url, '//')) {
            return route('notificaciones.index');
        }

        if (isset($parts['host'])) {
            $origin = parse_url(url('/'));
            if (!in_array($parts['scheme'] ?? '', ['http', 'https'], true)
                || strtolower($parts['host']) !== strtolower($origin['host'] ?? '')
                || ($parts['port'] ?? null) !== ($origin['port'] ?? null)
                || isset($parts['user']) || isset($parts['pass'])) {
                return route('notificaciones.index');
            }
            $url = ($parts['path'] ?? '/').(isset($parts['query']) ? '?'.$parts['query'] : '').(isset($parts['fragment']) ? '#'.$parts['fragment'] : '');
        }

        return str_starts_with($url, '/') && !str_starts_with($url, '//') ? $url : route('notificaciones.index');
    }
}
