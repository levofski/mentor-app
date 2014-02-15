<?php
/**
 * @author Matt Frost <mfrost.design@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package MentorApp
 */
namespace MentorApp;

class UserSerializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * setup method - runs before each test
     */
    public function setUp()
    {
        $this->serializer = new UserArraySerializer();
    }

    /**
     * teardown method - runs after each test
     */
    public function tearDown()
    {
        unset($this->serializer);
    }

    /**
     * Test to ensure that passing in a user object returns an array
     * correctly
     */
    public function testUserObjectReturnsArray()
    {
        $user = new User();
        $user->id = '123bcdae32';
        $user->firstName = 'Testy';
        $user->lastName = 'McTesterson';
        $user->email = 'testy@gmail.com';
        $user->githubHandle = 'testy123';
        $user->ircNick = 'testy234';
        $user->twitterHandle = 'testy345';
        $user->mentorAvailable = true;
        $user->apprenticeAvailable = false;
        
        $userArray = $this->serializer->toArray($user);
        $this->assertEquals($user->id, $userArray['id']);
        $this->assertEquals($user->firstName, $userArray['first_name']);
        $this->assertEquals($user->lastName, $userArray['last_name']);
        $this->assertEquals($user->email, $userArray['email']);
        $this->assertEquals($user->githubHandle, $userArray['github_handle']);
        $this->assertEquals($user->ircNick, $userArray['irc_nick']);
        $this->assertEquals($user->twitterHandle, $userArray['twitter_handle']);
        $this->assertEquals($user->mentorAvailable, $userArray['mentor_available']);
        $this->assertEquals($user->apprenticeAvailable, $userArray['apprentice_available']);
    }

    /**
     * Test to ensure if an instance of something other than user is passed in, an empty array
     * is returned
     */
    public function testWrongTypeReturnsEmptyArray()
    {
        $user = new \DateTime();
        $array = $this->serializer->toArray($user);
        $this->assertEmpty($array);
    }

    /**
     * Test to ensure when a user array is provided to fromArray a User instance
     * is returned
     */
    public function testUserArrayProvidersUserInstance()
    {
        $user = array(
            'id' => '123bedc093',
            'first_name' => 'Tests',
            'last_name' => 'McGee',
            'email' => 'tests.mcgee@gamil.com',
            'github_handle' => 'testsMcGee',
            'irc_nick' => 'testsMcGee123',
            'twitter_handle' => 'testsMcGee234',
            'mentor_available' => false,
            'apprentice_available' => true
        );

        $userObject = $this->serializer->fromArray($user);
        $this->assertEquals($userObject->id, $user['id']);
        $this->assertEquals($userObject->firstName, $user['first_name']);
        $this->assertEquals($userObject->lastName, $user['last_name']);
        $this->assertEquals($userObject->email, $user['email']);
        $this->assertEquals($userObject->githubHandle, $user['github_handle']);
        $this->assertEquals($userObject->ircNick, $user['irc_nick']);
        $this->assertEquals($userObject->twitterHandle, $user['twitter_handle']);
        $this->assertEquals($userObject->mentorAvailable, $user['mentor_available']);
        $this->assertEquals($userObject->apprenticeAvailable, $user['apprentice_available']);
    }

    /**
     * Test to ensure a collection of instances are serialized correctly
     */
    public function testEnsureCollectionIsSerializedCorrectly()
    {
        $user = new User();
        $user->id = '1234b678ed';
        $user->firstName = 'test';
        $user->lastName = 'mctesty';
        $user->email = 'test@testy.com';
        
        $stdClass = new \stdClass();
        $stdClass->id = '66543dedab';
        $stdClass->firstName = 'Wrongy';
        $stdClass->lastName = 'McWrongerson';
        $stdClass->email = 'wrongy@wrongy.com';

        $collection = [$user, $stdClass];
        $serializer = new UserArraySerializer();
        $result = $serializer->collectionToArray($collection);

        $this->assertEmpty($result[1]);
        $this->assertEquals($result[0]['id'], $user->id);
        $this->assertEquals($result[0]['email'], $user->email);
    }
}
