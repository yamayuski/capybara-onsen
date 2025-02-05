<?php

declare(strict_types=1);

/**
 * Class Uri
 * @package CapybaraOnsen\HttpMessage\Internal
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace CapybaraOnsen\HttpMessage\Internal;

use CapybaraOnsen\HttpMessage\MalformedUriException;
use CapybaraOnsen\HttpMessage\Uri;

/**
 * Parse URI to parts
 * @package CapybaraOnsen\HttpMessage\Internal
 * @internal
 */
final class UriPartsParser
{
    /**
     * Parse any array to URI parts
     * @param array<array-key, mixed> $parts
     * @return array{scheme: string, user: string, pass: string, host: string, port: ?int, path: string, query: string, fragment: string}
     */
    public static function parseFromArray(array $parts): array
    {
        return [
            'scheme' => \array_key_exists('scheme', $parts) ? self::filterScheme($parts['scheme']) : '',
            'user' => \array_key_exists('user', $parts) ? self::filterUser($parts['user']) : '',
            'pass' => \array_key_exists('pass', $parts) ? self::filterPass($parts['pass']) : '',
            'host' => \array_key_exists('host', $parts) ? self::filterHost($parts['host']) : '',
            'port' => \array_key_exists('port', $parts) ? self::filterPort($parts['port']) : null,
            'path' => \array_key_exists('path', $parts) ? self::filterPath($parts['path']) : '',
            'query' => \array_key_exists('query', $parts) ? self::filterQuery($parts['query']) : '',
            'fragment' => \array_key_exists('fragment', $parts) ? self::filterFragment($parts['fragment']) : '',
        ];
    }

    /**
     * Parse URI string to parts
     * @param string $uri
     * @return array{scheme: string, user: string, pass: string, host: string, port: ?int, path: string, query: string, fragment: string}
     */
    public static function parseFromString(string $uri): array
    {
        return self::parseFromArray(self::parseUrl($uri));
    }

    /**
     * Parse new URI parts with Uri instance
     * @param Uri $uri Current Uri
     * @param array<array-key, mixed> $newParts
     * @return array{scheme: string, user: string, pass: string, host: string, port: ?int, path: string, query: string, fragment: string}
     */
    public static function parseWithNewParts(Uri $uri, array $newParts): array
    {
        return self::parseFromArray([...self::parseFromString(((string) $uri)), ...$newParts]);
    }

    /**
     * It respects GuzzleHTTP's parser for multibyte domain names.
     * @see https://www.php.net/manual/en/function.parse-url.php#114817
     * @see https://github.com/guzzle/psr7/blob/38ef514a6c21335f29d9be64b097d2582ecbf8e4/src/Uri.php#L106
     * @param string $url
     * @return array<string, string>
     * @throws MalformedUriException
     */
    private static function parseUrl(string $url): array
    {
        // @see https://github.com/guzzle/psr7/pull/403
        /** @var string[] $matches */
        $matches = [];
        $prefix = null;
        $urlMayExceptPrefix = $url;
        $foundIpv6 = \preg_match(
            '%^(.*://\[[0-9:a-f]+\])(.*?)$%', // Whether host is IPv6
            $url,
            $matches,
        );
        if ($foundIpv6) {
            // The host is IPv6
            \assert(\count($matches) === 3);
            $prefix = $matches[1];
            $urlMayExceptPrefix = $matches[2];
        }

        $encodedUrl = \preg_replace_callback(
            '%[^:/@?&=#]+%usD', // multibyte characters
            static fn (array $matches): string => \urlencode($matches[0]),
            $urlMayExceptPrefix,
        );

        $parseResult = \parse_url($prefix . $encodedUrl);

        if ($parseResult === false) {
            throw new MalformedUriException('Seriously malformed URI has provided: ' . $url);
        }

        return \array_map(
            static fn (string|int $value): string => \urldecode((string)$value),
            $parseResult,
        );
    }

    /**
     * Filter scheme part
     * It does not reject unknown scheme. It only trims and lower case.
     * @param mixed $scheme
     * @return string
     */
    private static function filterScheme(mixed $scheme): string
    {
        if (\is_string($scheme)) {
            return \strtolower(\trim($scheme, ':/'));
        }
        return '';
    }

    private static function filterUser(mixed $user): string
    {
    }

    private static function filterPass(mixed $pass): string
    {
    }

    private static function filterHost(mixed $host): string
    {
    }

    private static function filterPort(mixed $port): ?int
    {
    }

    private static function filterPath(mixed $path): string
    {
    }

    private static function filterQuery(mixed $query): string
    {
    }

    private static function filterFragment(mixed $fragment): string
    {
    }
}
