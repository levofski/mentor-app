<?php
/**
 * @author Kevin Crawley <kcmastrpc@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package MentorApp
 */

namespace MentorApp;

class Auth {
    private $app;

    public function __construct(\Slim\Slim $app) {
        $this->app = $app;
    }

    /**
     * Starts the OAuth cascade. Initially this checks to see if there is a cookie set with token associates, if so,
     * it will validate based on that. If not, it will create a new association and return the authorization status
     * based on which action it takes. There are dragons here, so tread lightly.
     *
     * @param $type
     * @param array $scope
     * @param \fkooman\OAuth\Client\ClientConfig $clientConfig
     * @return bool|string
     * @throws \Exception
     * @throws BearerErrorResponseException
     */
    public function initialOAuth($type, array $scope, \fkooman\OAuth\Client\ClientConfig $clientConfig) {
        $tokenStorage = new \fkooman\OAuth\Client\PdoStorage($this->app->db);
        $httpClient = new \Guzzle\Http\Client();

        $api = new \fkooman\OAuth\Client\Api("MentorApp", $clientConfig, $tokenStorage, $httpClient);

        // generates key token for use in session tracking
        if (!isset($_SESSION['oauth_session'])) {
            $_SESSION['oauth_session'] = array(
                'key'   => substr(md5(time() . rand()), 0, 8),
                'type'  => $type
            );
        }

        // generates context for this oauth_session
        $context = new \fkooman\OAuth\Client\Context($_SESSION['oauth_session']['key'], $scope);

        $accessToken = $api->getAccessToken($context);

        if (false === $accessToken) {
            /* no valid access token available, go to authorization server */
            return $api->getAuthorizeUri($context);
        } else {
            try {
                /* we found a token, lets validate it */
                $client = new \Guzzle\Http\Client();
                $bearerAuth = new \fkooman\Guzzle\Plugin\BearerAuth\BearerAuth($accessToken->getAccessToken());
                $client->addSubscriber($bearerAuth);

                return true;
            } catch (BearerErrorResponseException $e) {
                if ("invalid_token" === $e->getBearerReason()) {
                    // the token we used was invalid, possibly revoked, we throw it away
                    $api->deleteAccessToken($context);
                    $api->deleteRefreshToken($context);

                    /* no valid access token available, go to authorization server */
                    return $api->getAuthorizeUri($context);
                }
                throw $e;
            }
        }

        return false;
    }

    /**
     * Handles the response and storage of tokens from an OAuth callback, magic things happen here - don't ask me what.
     *
     * @param \fkooman\OAuth\Client\ClientConfig $clientConfig
     * @return bool
     */
    public function callbackOAuth(\fkooman\OAuth\Client\ClientConfig $clientConfig) {
        try {
            $tokenStorage = new \fkooman\OAuth\Client\PdoStorage($this->app->db);
            $httpClient = new \Guzzle\Http\Client();

            $cb = new \fkooman\OAuth\Client\Callback("MentorApp", $clientConfig, $tokenStorage, $httpClient);

            $cb->handleCallback($_GET);

            /** @todo :: either we setup associating the user here or let the client side handle making the
             * associations and associated queries **/

            return true;
        } catch (AuthorizeException $e) {
            // this exception is thrown by Callback when the OAuth server returns a
            // specific error message for the client, e.g.: the user did not authorize
            // the request
            echo sprintf("ERROR: %s, DESCRIPTION: %s", $e->getMessage(), $e->getDescription());
        } catch (\Exception $e) {
            // other error, these should never occur in the normal flow
            echo sprintf("ERROR: %s", $e->getMessage());
        }

        return false;
    }

    /**
     * Fetches a stored oauth token based on the session based unique id
     *
     * @return bool|string
     */
    public function getToken() {
        if (isset($_SESSION['oauth_session']) && $_SESSION['oauth_session']['type'] == 'github') {
            try {
                $query = 'SELECT access_token FROM access_tokens WHERE user_id = :user_id';
                $statement = $this->app->db->prepare($query);
                $statement->execute(array('user_id' => $_SESSION['oauth_session']['key']));
                $queryData = $statement->fetch();

                if ($statement->rowCount() < 1) {
                    return false;
                } else {
                    return $queryData['access_token'];
                }
            } catch (\PDOException $e) {
                // log the error
                return false;
            }
        }
        return false;
    }
}