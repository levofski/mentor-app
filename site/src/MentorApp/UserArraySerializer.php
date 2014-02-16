<?php
/**
 * @author Matt Frost <mfrost.design@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package MentorApp;
 */
namespace MentorApp;

class UserArraySerializer implements SerializerInterface
{
    /**
     * Format method to satisfy the interface contract
     *
     * @return string representation of the user object
     */
    public function toArray($user)
    {
        $response = [];
        if (!$user instanceof \MentorApp\User) {
            return $response;
        }
        $response['id'] = htmlspecialchars($user->id);
        $response['first_name'] = htmlspecialchars($user->firstName);
        $response['last_name'] = htmlspecialchars($user->lastName);
        $response['email'] = htmlspecialchars($user->email);
        $response['github_handle'] = htmlspecialchars($user->githubHandle);
        $response['irc_nick'] = htmlspecialchars($user->ircNick);
        $response['twitter_handle'] = htmlspecialchars($user->twitterHandle);
        $response['mentor_available'] = htmlspecialchars($user->mentorAvailable);
        $response['apprentice_available'] = htmlspecialchars($user->apprenticeAvailable);
        return $response; 
    }

    /**
     * fromArray converts an array to an instance of user
     *
     * @param array $userArray an array of user information
     * @return \MentorApp\User
     */
    public function fromArray(Array $userArray)
    {
        $user = new User();
        $user->id = (isset($userArray['id'])) ? $userArray['id'] : null;
        $user->firstName = (isset($userArray['first_name'])) ? $userArray['first_name'] : null;
        $user->lastName = (isset($userArray['last_name'])) ? $userArray['last_name'] : null;
        $user->email = (isset($userArray['email'])) ? $userArray['email'] : null;
        $user->githubHandle = (isset($userArray['github_handle'])) ? $userArray['github_handle'] : null;
        $user->ircNick = (isset($userArray['irc_nick'])) ? $userArray['irc_nick'] : null;
        $user->twitterHandle = (isset($userArray['twitter_handle'])) ? $userArray['twitter_handle'] : null;
        $user->mentorAvailable = (isset($userArray['mentor_available'])) ? $userArray['mentor_available'] : null;
        $user->apprenticeAvailable = (isset($userArray['apprentice_available'])) ? $userArray['apprentice_available'] : null;
        return $user;
    }

    /**
     * Converts a collection of user objects into an array
     *
     * @param array $collection a collection of users
     * @return array an array representation of those instances
     */
    public function collectionToArray(array $collection)
    {
        $output = [];
        foreach ($collection as $user) {
            $output[] = $this->toArray($user);
        }
        return $output;
    }
}
 
