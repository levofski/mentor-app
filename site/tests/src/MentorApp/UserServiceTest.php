<?php
namespace MentorApp;
class UserServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Create resources and mocks that will be used by the tests
     */
    public function setUp()
    {
        $this->db = $this->getMock('\PDOTestHelper', array('prepare'));
        $this->statement = $this->getMock('\PDOStatement', array('execute', 'fetch', 'fetchAll', 'rowCount'));
        $this->mockData = array();
        $this->mockData['id'] = '';
        $this->mockData['first_name'] = 'Test';
        $this->mockData['last_name'] = 'User';
        $this->mockData['email'] = 'testuser@gmail.com';
        $this->mockData['github_handle'] = 'testuser';
        $this->mockData['irc_nick'] = 'testuser';
        $this->mockData['twitter_handle'] = '@testuser';
        $this->mockData['mentor_available'] = true;
        $this->mockData['apprentice_available'] = false;
        $this->mockData['timezone'] = 'America/Chicago';
    }

    /**
     * Destroy all the resources after each test
     */
    public function tearDown()
    {
        unset($this->db);
        unset($this->statement);
        unset($this->mockData);
    }

    /**
     * Ensure that an empty string paramenter will return null from the user service
     */
    public function testUserWithNoIDReturnsNull()
    {
        $id = '';
        $userService = new UserService($this->db);
        $returnedUser = $userService->retrieve($id);
        $this->assertNull($returnedUser, "Did not return a null");
    }

    /**
     * Ensure that a User object with an id will run the query and return
     * a fully populated User object
     */
    public function testUserWithIdReturnsPopulatedUser()
    {
        $id = 'abc123def4';
        $this->mockData['id'] = $id;
        $this->mockData['first_name'] = 'Mike';
        $this->mockData['last_name'] = 'Jones';
        $this->mockData['email'] = 'mikejones@who.com';
        $expectedQuery = "SELECT id, first_name, last_name, email, github_handle, irc_nick, ";
        $expectedQuery .= "twitter_handle, mentor_available, apprentice_available, ";
        $expectedQuery .= "timezone FROM user WHERE id = :id";
        $teachingQuery = 'SELECT id_tag FROM teaching_skills WHERE id_user = :id';
        $learningQuery = 'SELECT id_tag FROM learning_skills WHERE id_user = :id';

        $this->db->expects($this->at(0))
            ->method('prepare')
            ->with($expectedQuery)
            ->will($this->returnValue($this->statement));

        $this->db->expects($this->at(1))
            ->method('prepare')
            ->with($teachingQuery)
            ->will($this->returnValue($this->statement));

        $this->db->expects($this->at(2))
            ->method('prepare')
            ->with($learningQuery)
            ->will($this->returnValue($this->statement));

        $this->statement->expects($this->exactly(3))
            ->method('execute')
            ->with(array('id' => $id))
            ->will($this->returnValue($this->statement));

        $this->statement->expects($this->at(1))
            ->method('fetch')
            ->will($this->returnValue($this->mockData));

        $this->statement->expects($this->at(2))
            ->method('rowCount')
            ->will($this->returnValue(1));

        $this->statement->expects($this->at(3))
            ->method('fetch')
            ->will($this->returnValue(array('id_tag' => 'skill')));

        $this->statement->expects($this->at(4))
            ->method('fetch')
            ->will($this->returnValue(array('id_tag' => 'skill')));
    

        $userService = new UserService($this->db);
        $returnedUser = $userService->retrieve($id);
        $this->assertEquals(
            $this->mockData['id'],
            $returnedUser->id,
            'ID was not the same'
        );
        $this->assertEquals(
            $this->mockData['first_name'],
            $returnedUser->firstName,
            'First name was not the same'
        );        
        $this->assertEquals(
            array('skill'),
            $returnedUser->teachingSkills,
            'Skills not assigned to user'
        );
    }

    /**
     * Test to ensure null is returned when PDO throws an exception
     * @expectedException \PDOException
     */
    public function testGetUserWhenPDOThrowsException()
    {
        $this->db->expects($this->once())
            ->method('prepare')
            ->will($this->throwException(new \PDOException));
        $id = 'bbccdd1134';
        $userService = new UserService($this->db);
        $retrievedUser = $userService->retrieve($id);
    }

    /**
     * Test to ensure that a new user can be created by the user service
     */
    public function testEnsureUserIsCreated()
    {
        $expectedQuery = 'INSERT INTO user (id, first_name, last_name, email, ';
        $expectedQuery .= 'github_handle, irc_nick, twitter_handle, mentor_available, ';
        $expectedQuery .= 'apprentice_available, ';
        $expectedQuery .= 'timezone) VALUES (:id, :first_name, :last_name, :email, ';
        $expectedQuery .= ':github_handle, :irc_nick, :twitter_handle, :mentor_available, ';
        $expectedQuery .= ':apprentice_available, :timezone)';
        $teachingQuery = 'INSERT INTO teaching_skills (id_user, id_tag) VALUES (:user, :tag)';
        $learningQuery = 'INSERT INTO learning_skills (id_user, id_tag) VALUES (:user, :tag)';
        $user = new User();
        $user->id = '1932abed12';
        $user->firstName = 'Test';
        $user->lastName = 'User';
        $user->email = 'test.user@gmail.com';
        $user->githubHandle = 'testuser';
        $user->ircNick = 'testUser';
        $user->twitterHandle = '@testUser';
        $user->mentor_available = true;
        $user->apprentice_available = false;
        $user->timezone = 'America/Chicago';
        $statementParams = array(
            'id' => $user->id,
            'first_name' => $user->firstName,
            'last_name' => $user->lastName,
            'email' => $user->email,
            'github_handle' => $user->githubHandle,
            'irc_nick' => $user->ircNick,
            'twitter_handle' => $user->twitterHandle,
            'mentor_available' => $user->mentorAvailable,
            'apprentice_available' => $user->apprenticeAvailable,
            'timezone' => $user->timezone,
        );

        $this->db->expects($this->at(0))
            ->method('prepare')
            ->with('SELECT id FROM `user` WHERE id = :id')
            ->will($this->returnValue($this->statement));

        $this->db->expects($this->at(1))
            ->method('prepare')
            ->with($expectedQuery)
            ->will($this->returnValue($this->statement));

        $this->db->expects($this->at(2))
            ->method('prepare')
            ->with($teachingQuery)
            ->will($this->returnValue($this->statement));

        $this->db->expects($this->at(3))
            ->method('prepare')
            ->with($learningQuery)
            ->will($this->returnValue($this->statement));

        $this->statement->expects($this->at(0))
            ->method('execute')
            ->with($this->isType('array'))
            ->will($this->returnValue($this->statement));

       $this->statement->expects($this->once())
            ->method('rowCount')
            ->will($this->returnValue(0));

        $this->statement->expects($this->at(2))
            ->method('execute')
            ->with($this->arrayHasKey('id'))
            ->will($this->returnValue($this->statement));

        $userService = new UserService($this->db);
        $savedUser = $userService->create($user);
        $this->assertNotNull($savedUser);
        $this->assertNotEmpty($savedUser->id);
    }

    /**
     * Test to ensure that PDOException causes the UserService::create to return null 
     * @expectedException \PDOException
     */
    public function testPDOExceptionCausesServiceToReturnNull()
    {
        $this->db->expects($this->at(0))
            ->method('prepare')
            ->with('SELECT id FROM `user` WHERE id = :id')
            ->will($this->returnValue($this->statement));

        $this->statement->expects($this->once())
            ->method('execute')
            ->with($this->isType('array'))
            ->will($this->returnValue($this->statement));

        $this->statement->expects($this->once())
            ->method('rowCount')
            ->will($this->returnValue(0));

        $this->db->expects($this->at(1))
            ->method('prepare')
            ->will($this->throwException(new \PDOException));
        $user = new User();
        $user->id = '123abcde45';
        $user->firstName = 'Test';
        $user->lastName = 'User';
        $user->email = 'test.user@gmail.com';
        $user->githubHandle = 'testuser';
        $user->ircNick = 'testUser';
        $user->twitterHandle = '@testUser';
        $user->mentorAvailable = true;
        $user->apprenticeAvailable = false;
        $user->timezone = 'America/Chicago';
        $userService = new UserService($this->db);
        $result = $userService->create($user);
    }

    /**
     * Test to ensure a user can be updated properly
     */
    public function testUserUpdate()
    {
        // create a user
        $user = new User();
        $user->id = '123abcde45';
        $user->firstName = 'Test';
        $user->lastName = 'User';
        $user->email = 'test.user@gmail.com';
        $user->githubHandle = 'testUser';
        $user->ircNick = 'testuser';
        $user->twitterHandle = '@testUser';
        $user->mentorAvailable = true;
        $user->apprenticeAvailable = false;
        $user->timezone = 'America/Chicago';

        //build the array for the execute method
        $valueArray = array();
        $valueArray['id'] = $user->id;
        $valueArray['first_name'] = $user->firstName;
        $valueArray['last_name'] = $user->lastName;
        $valueArray['email'] = $user->email;
        $valueArray['github_handle'] = $user->githubHandle;
        $valueArray['irc_nick'] = $user->ircNick;
        $valueArray['twitter_handle'] = $user->twitterHandle;
        $valueArray['mentor_available'] = $user->mentorAvailable;
        $valueArray['apprentice_available'] = $user->apprenticeAvailable;
        $valueArray['timezone'] = $user->timezone;

        // build the expected query for user
        $expectedQuery = "UPDATE user SET id=:id, first_name=:first_name, last_name=:last_name, ";
        $expectedQuery .= "email=:email, github_handle=:github_handle, irc_nick=:irc_nick, twitter_handle=:twitter_handle, ";
        $expectedQuery .= "mentor_available=:mentor_available, apprentice_available=:apprentice_available, ";
        $expectedQuery .= "timezone=:timezone WHERE id=:id";

        $this->db->expects($this->at(0))
            ->method('prepare')
            ->with($expectedQuery)
            ->will($this->returnValue($this->statement));

        $this->statement->expects($this->at(0))
            ->method('execute')
            ->with($valueArray)
            ->will($this->returnValue($this->statement));

        $this->statement->expects($this->once())
            ->method('rowCount')
            ->will($this->returnValue(1));

        $this->db->expects($this->at(1))
            ->method('prepare')
            ->with('DELETE FROM teaching_skills WHERE id_user = :id')
            ->will($this->returnValue($this->statement));

        $this->db->expects($this->at(2))
            ->method('prepare')
            ->with('DELETE FROM learning_skills WHERE id_user = :id')
            ->will($this->returnValue($this->statement));

        $this->statement->expects($this->at(2))
            ->method('execute')
            ->with(array('id' => $user->id));

        $userService = new UserService($this->db);
        $this->assertTrue($userService->update($user));
    }

    /**
     * Test to ensure false is returned when affected rows is 0
     */
    public function testUserUpdateReturnsFalse()
    {
        // create a user
        $user = new User();
        $user->id = '123abcde45';
        $user->firstName = 'Test';
        $user->lastName = 'User';
        $user->email = 'test.user@gmail.com';
        $user->githubHandle = 'testuser';
        $user->ircNick = 'testUser';
        $user->twitterHandle = '@testUser';
        $user->mentorAvailable = true;
        $user->apprenticeAvailable = false;
        $user->timezone = 'America/Chicago';

        //build the array for the execute method
        $valueArray = array();
        $valueArray['id'] = $user->id;
        $valueArray['first_name'] = $user->firstName;
        $valueArray['last_name'] = $user->lastName;
        $valueArray['email'] = $user->email;
        $valueArray['github_handle'] = $user->githubHandle;
        $valueArray['irc_nick'] = $user->ircNick;
        $valueArray['twitter_handle'] = $user->twitterHandle;
        $valueArray['mentor_available'] = $user->mentorAvailable;
        $valueArray['apprentice_available'] = $user->apprenticeAvailable;
        $valueArray['timezone'] = $user->timezone;

        // build the expected query for user
        $expectedQuery = "UPDATE user SET id=:id, first_name=:first_name, last_name=:last_name, ";
        $expectedQuery .= "email=:email, github_handle=:github_handle, irc_nick=:irc_nick, twitter_handle=:twitter_handle, ";
        $expectedQuery .= "mentor_available=:mentor_available, apprentice_available=:apprentice_available, ";
        $expectedQuery .= "timezone=:timezone WHERE id=:id";

        $this->db->expects($this->at(0))
            ->method('prepare')
            ->with($expectedQuery)
            ->will($this->returnValue($this->statement));

        $this->statement->expects($this->at(0))
            ->method('execute')
            ->with($valueArray)
            ->will($this->returnValue($this->statement));

        $this->statement->expects($this->once())
            ->method('rowCount')
            ->will($this->returnValue(0));

        $this->db->expects($this->at(1))
            ->method('prepare')
            ->with('DELETE FROM teaching_skills WHERE id_user = :id')
            ->will($this->returnValue($this->statement));

        $this->db->expects($this->at(2))
            ->method('prepare')
            ->with('DELETE FROM learning_skills WHERE id_user = :id')
            ->will($this->returnValue($this->statement));

        $this->statement->expects($this->at(2))
            ->method('execute')
            ->with(array('id' => $user->id));

        $userService = new UserService($this->db);
        $this->assertFalse($userService->update($user));
    }

    /**
     * Test for the setMapping method
     */
    public function testMappingCanBeSet()
    {
        $userService = new UserService($this->db);
        $mapping = array(
            'first_name' => 'firstName',
            'last_name' => 'lastName'
        );
        $userService->setMapping($mapping);
        $retrieved = $userService->getMapping();
        $this->assertSame($mapping, $retrieved);
    }

    /**
     * test to ensure that if no records come back in retrieve
     * that null is returned
     */
    public function testNullIsReturnedIfNoResultsAreFound()
    {
        $id = '1bcde23bcd';
        $expectedQuery = "SELECT id, first_name, last_name, email, github_handle, irc_nick, ";
        $expectedQuery .= "twitter_handle, mentor_available, apprentice_available, ";
        $expectedQuery .= "timezone FROM user WHERE id = :id";

        $this->db->expects($this->once())
            ->method('prepare')
            ->with($expectedQuery)
            ->will($this->returnValue($this->statement));

        $this->statement->expects($this->at(0))
            ->method('execute')
            ->with(['id' => $id])
            ->will($this->returnValue($this->statement));

        $this->statement->expects($this->at(1))
            ->method('fetch');

        $this->statement->expects($this->at(2))
            ->method('rowCount')
            ->will($this->returnValue(0));

        $userService = new UserService($this->db);
        $user = $userService->retrieve($id);
        $this->assertNull($user);
    }

    public function testExistsThrowsProperExceptionIfEmptyId()
    {
        $this->setExpectedException('RuntimeException');

        $userService = new UserService($this->db);
        $userService->exists('');
    }

    public function testExistsReturnsTrueWithProperId()
    {
        $id = '1bcde23bcd';
        $expectedQuery = "SELECT id FROM `user` WHERE id = :id";

        $this->db->expects($this->once())
            ->method('prepare')
            ->with($expectedQuery)
            ->will($this->returnValue($this->statement));

        $this->statement->expects($this->at(0))
            ->method('execute')
            ->with(['id' => $id])
            ->will($this->returnValue($this->statement));

        $this->statement->expects($this->at(1))
            ->method('rowCount')
            ->will($this->returnValue(1));

        $userService = new UserService($this->db);
        $userExists = $userService->exists($id);
        $this->assertTrue($userExists);
    }

    public function testExistsReturnsFalseWithImproperId()
    {
        $id = '1bcde23bcd';
        $expectedQuery = "SELECT id FROM `user` WHERE id = :id";

        $this->db->expects($this->once())
            ->method('prepare')
            ->with($expectedQuery)
            ->will($this->returnValue($this->statement));

        $this->statement->expects($this->at(0))
            ->method('execute')
            ->with(['id' => $id])
            ->will($this->returnValue($this->statement));

        $this->statement->expects($this->at(1))
            ->method('rowCount')
            ->will($this->returnValue(0));

        $userService = new UserService($this->db);
        $userExists = $userService->exists($id);
        $this->assertFalse($userExists);
    }

    public function testDeleteReturnsTrueWithProperId()
    {
        $id = '1bcde23bcd';
        $expectedQuery = "DELETE FROM user WHERE id = :id";

        $this->db->expects($this->at(0))
            ->method('prepare')
            ->with($expectedQuery)
            ->will($this->returnValue($this->statement));

        $this->statement->expects($this->at(0))
            ->method('execute')
            ->with(["id" => $id])
            ->will($this->returnValue($this->statement));

        $this->statement->expects($this->once())
            ->method('rowCount')
            ->will($this->returnValue(1));

        $this->db->expects($this->at(1))
            ->method('prepare')
            ->with('DELETE FROM teaching_skills WHERE id_user = :id')
            ->will($this->returnValue($this->statement));

        $this->db->expects($this->at(2))
            ->method('prepare')
            ->with('DELETE FROM learning_skills WHERE id_user = :id')
            ->will($this->returnValue($this->statement));

        $this->statement->expects($this->at(2))
            ->method('execute')
            ->with(['id' => $id]);

        $userService = new UserService($this->db);
        $userDeleted = $userService->delete($id);
        $this->assertTrue($userDeleted);
    }

    public function testDeleteReturnsFalseWithImproperId()
    {
        $id = '1bcde23bcd';
        $expectedQuery = "DELETE FROM user WHERE id = :id";

        $this->db->expects($this->at(0))
            ->method('prepare')
            ->with($expectedQuery)
            ->will($this->returnValue($this->statement));

        $this->statement->expects($this->at(0))
            ->method('execute')
            ->with(["id" => $id])
            ->will($this->returnValue($this->statement));

        $this->statement->expects($this->once())
            ->method('rowCount')
            ->will($this->returnValue(0));

        $userService = new UserService($this->db);
        $userDeleted = $userService->delete($id);
        $this->assertFalse($userDeleted);
    }

    /**
     * Test to ensure the UserService can retrieve all the Users with pagination
     */
    public function testRetrieveAllUsersFromService()
    {
        $teachingQuery = 'SELECT id_tag FROM teaching_skills WHERE id_user = :id';
        $learningQuery = 'SELECT id_tag FROM learning_skills WHERE id_user = :id';
        $data = array(
            array('id' => '12345bcdef', 'first_name' => 'Matt', 'last_name' => 'Frost'),
            array('id' => 'bbcde54e3a', 'first_name' => 'Test', 'last_name' => 'User'),
        );

        $this->db->expects($this->at(0))
            ->method('prepare')
            ->with($this->stringContains('LIMIT 0, 20'))
            ->will($this->returnValue($this->statement));
        $this->statement->expects($this->at(0))
            ->method('execute')
            ->will($this->returnValue($this->statement));
        $this->statement->expects($this->at(1))
            ->method('fetchAll')
            ->will($this->returnValue($data));

        $this->db->expects($this->at(1))
            ->method('prepare')
            ->with($teachingQuery)
            ->will($this->returnValue($this->statement));
        $this->statement->expects($this->at(2))
            ->method('execute')
            ->with(['id' => $data[0]['id']])
            ->will($this->returnValue($this->statement));
        $this->statement->expects($this->at(3))
            ->method('fetch')
            ->will($this->returnValue(['id_tag' => 'bbce15ed69']));

        $this->db->expects($this->at(2))
            ->method('prepare')
            ->with($learningQuery)
            ->will($this->returnValue($this->statement));
        $this->statement->expects($this->at(5))
            ->method('execute')
            ->with(['id'=> $data[0]['id']])
            ->will($this->returnValue($this->statement));
        $this->statement->expects($this->at(6))
            ->method('fetch')
            ->will($this->returnValue(['id_tag' => '123455beda']));

        $this->db->expects($this->at(3))
            ->method('prepare')
            ->with($teachingQuery)
            ->will($this->returnValue($this->statement));
        $this->statement->expects($this->at(8))
            ->method('execute')
            ->with(['id' => $data[1]['id']])
            ->will($this->returnValue($this->statement));
        $this->statement->expects($this->at(9))
            ->method('fetch')
            ->will($this->returnValue(['id_tag' => 'bbce15ed69']));

        $this->db->expects($this->at(4))
            ->method('prepare')
            ->with($learningQuery)
            ->will($this->returnValue($this->statement));
        $this->statement->expects($this->at(11))
            ->method('execute')
            ->with(['id'=> $data[1]['id']])
            ->will($this->returnValue($this->statement));
        $this->statement->expects($this->at(12))
            ->method('fetch')
            ->will($this->returnValue(['id_tag' => '123455beda']));
        $service = new UserService($this->db);
        $results = $service->retrieveAll();
        $this->assertEquals($data[0]['first_name'], $results[0]->firstName);
        $this->assertEquals($data[1]['last_name'], $results[1]->lastName);
        $this->assertEquals($data[0]['id'], $results[0]->id);
        $this->assertEquals($data[1]['id'], $results[1]->id);
    } 

    /**
     * Ensure that if the wrong skill type is provided an InvalidArgumentException is thrown
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidArgumentIsThrownOnInvalidSkillType()
    {
        $userService = new UserService($this->db);
        $userService->searchBySkill('PHP', 'learnding');
    }            

}
