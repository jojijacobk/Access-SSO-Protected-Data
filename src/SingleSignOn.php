<?php

/**
 *  SingleSignOn - tries to access the single-sign-on protected URL, and follows HTTP redirects to the authentication server,
 *                 and pass through authentication server by posting credentials configured in config.ini file.
 *                 After successful authentication, a cookie jar stores necessary cookies to perform subsequent visits to any URLs within the protected host.
 *
 * @author Joji Jacob <joji.jacob.k@gmail.com>
 */

declare(strict_types=1);

namespace jojijacobk\access_sso_protected_data;

class SingleSignOn
{
    private $cookieJar;
    private $client;
    private $ssoUsername;
    private $ssoPassword;

    /**
     * SingleSignOn constructor.
     * @param $requestUrl - the original url we want to access
     * @param string $configFile - where the configurations such as SSO credentials are stored
     */
    public function __construct($requestUrl, $configFile = 'config.ini')
    {
        $this->cookieJar = new \GuzzleHttp\Cookie\CookieJar;
        $this->client = new \GuzzleHttp\Client(['cookies' => true]);
        $this->readConfiguration($configFile);
        $this->performSingleSignOn($requestUrl);
    }

    /**
     * @param $configFile - where the configurations such as SSO credentials are stored
     */
    private function readConfiguration($configFile)
    {
        $config = parse_ini_file($configFile, true);
        $this->ssoUsername = $config['single_sign_on']['username'];
        $this->ssoPassword = $config['single_sign_on']['password'];
    }

    /**
     * @param $requestUrl - the original url we want to access
     * @return bool
     */
    private function performSingleSignOn($requestUrl)
    {
        // when you visit a url in protected server, you will be asked to visit sso authentication url to perform authentication
        $authentication_url = $this->requestProtectedResource($requestUrl);
        // once you are authenticated, you will be asked to visit your initially protected server along with the authenticated cookie jar :)
        if ($authentication_url) {
            $protected_host_url = $this->authenticate($authentication_url);
        }
        // when you walk into the protected server with your jar of required cookies, you are happily welcomed.
        if ($protected_host_url) {
            $this->authorizeUserToProtectedHost($protected_host_url);
            return true;
        }
        return false;
    }

    /**
     * @param $url
     * @return bool
     */
    private function requestProtectedResource($url)
    {
        $response = $this->client->request('GET', $url, [
            'cookies' => $this->cookieJar,
            'allow_redirects' => false
        ]);

        if (302 == $response->getStatusCode()) {
            $headers = $response->getHeaders();
            $location = $headers['Location'];
            return $location[0];
        }
        return false;
    }

    /**
     * @param $url
     * @return bool
     */
    private function authenticate($url)
    {
        $response = $this->client->request('POST', $url, [
            'cookies' => $this->cookieJar,
            'allow_redirects' => false,
            'auth' => [$this->ssoUsername, $this->ssoPassword]
        ]);

        if (302 == $response->getStatusCode()) {
            $headers = $response->getHeaders();
            $location = $headers['Location'];
            return $location[0];
        }
        return false;
    }

    /**
     * @param $url
     */
    private function authorizeUserToProtectedHost($url)
    {
        $this->client->request('GET', $url, [
            'cookies' => $this->cookieJar,
        ]);
    }

    /**
     * @return \GuzzleHttp\Cookie\CookieJar
     */
    public function getCookieJar()
    {
        return $this->cookieJar;
    }

}