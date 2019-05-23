<?php

/**
 *  Data Streamer - is used to perform read/write operations on protected server resources
 * 
 * @author Joji Jacob <joji.jacob.k@gmail.com>
 */

declare(strict_types=1);

namespace jojijacobk\access_sso_protected_data;

class DataStreamer
{
    private static $sso;
    private static $ssoCookieJar = [];
    private static $client;
    private static $authorizedHosts = [];

    /**
     * DataStreamer private constructor. We don't want anyone to instantiate DataStreamer. Just call it's public static methods.
     */
    private function __construct()
    {
    }

    /**
     * @param $requestUrl
     * @return bool
     */
    private static function isHostAuthorized($requestUrl)
    {
        $host = parse_url($requestUrl, PHP_URL_HOST);
        return (!empty(self::$authorizedHosts) && in_array($host,self::$authorizedHosts));
    }

    /**
     * @param $requestUrl
     */
    private static function authorizeHost($requestUrl)
    {
        $host = parse_url($requestUrl, PHP_URL_HOST);
        self::$sso = new SingleSignOn($requestUrl);
        self::$ssoCookieJar[$host] = self::$sso->getCookieJar();
        self::$authorizedHosts[] = $host;
    }

    /**
     * @return \GuzzleHttp\Client
     */
    private static function getClient()
    {
        if (is_null(self::$client)) {
            self::$client = new \GuzzleHttp\Client(['cookies' => true]);
        }
        return self::$client;
    }

    /**
     * @param $requestUrl
     * @return bool|string data
     */
    private static function requestData($requestUrl)
    {
        $host = parse_url($requestUrl, PHP_URL_HOST);
        $response = self::getClient()->request('GET', $requestUrl, [
            'cookies' => self::$ssoCookieJar[$host],
        ]);
        if (200 == $response->getStatusCode()) {
            $body = $response->getBody();
            return (string)$body;
        }
        return false;
    }

    /**
     * @param $requestUrl
     * @return bool|string
     */
    public static function read($requestUrl)
    {
        if (!self::isHostAuthorized($requestUrl)) {
            self::authorizeHost($requestUrl);
        }
        return self::requestData($requestUrl);
    }
}