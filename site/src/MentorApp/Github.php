<?php
namespace MentorApp;

/**
 * Interfaces with the Github API and retrieves data.
 *
 * Class Github
 * @author Kevin Crawley <kcmastrpc@gmail.com>
 * @package MentorApp
 */
class Github {
    private $app;
    private $token;

    public function __construct(\Slim\Slim $app, \MentorApp\Auth $auth) {
        $this->app = $app;
        $this->token = $auth->getToken();
    }

    /**
     * Queries the Github API for profile information
     *
     * @return bool|\Guzzle\Http\Message\Response
     */
    public function getProfile() {
        if ($this->token) {
            $queryUrl = "https://api.github.com/user";
            $client = new \Guzzle\Http\Client();
            return $client->get($queryUrl . "?access_token=" . $this->token)->send();
        }

        return false;
    }
}