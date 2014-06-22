<?php
/**
 * @author Matt Frost <mfrost.design@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package MentorApp
 */

namespace MentorApp;

/**
 * Class to interface with the data store and perform the necessary actions on 
 * the provided User instance
 */
class UserService
{
    /**
     * Using the hash trait
     */
    use Hash;

    /**
     * Constants for the skill types
     */
    const SKILL_TYPE_TEACHING = 'teaching';
    const SKILL_TYPE_LEARNING = 'learning';

    /**
     * @var \PDO db PDO instance to be used by the rest of the class
     */
    protected $db;

    /**
     * @var array mapping mapping of User properties to database fields
     */
    protected $mapping = array(
        'id' => 'id',
        'firstName' => 'first_name',
        'lastName' => 'last_name',
        'email' => 'email',
        'githubHandle' => 'github_handle',
        'ircNick' => 'irc_nick',
        'twitterHandle' => 'twitter_handle',
        'mentorAvailable' => 'mentor_available',
        'apprenticeAvailable' => 'apprentice_available',
        'timezone' => 'timezone'
    );

    /**
     * Constructor where the db and user instance are injected for testability
     *
     * @param \PDO db PDO instance
     */
    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Retrieve method to pull user information from the database and return a
     * the User instance populated with the correct information
     *
     * @param string id ID to search for and retrieve
     * @return \MentorApp\User user instance populated with the rest of the
     * @throws \PDOException
     * data
     */
    public function retrieve($id)
    {
        if (!$this->validateHash($id)) {
            return null;
        }

        $query = 'SELECT ' . implode(', ', $this->mapping) . ' FROM user WHERE id = :id';
        $statement = $this->db->prepare($query);
        $statement->execute(array('id' => $id));
        $userData = $statement->fetch();
        if ($statement->rowCount() < 1) {
            return null;
        }
        $user = $this->mapDatabaseResultToUser(new User(), $userData);
        $user->teachingSkills = $this->retrieveSkills($id, self::SKILL_TYPE_TEACHING);
        $user->learningSkills = $this->retrieveSkills($id, self::SKILL_TYPE_LEARNING);

        return $user;
    }

    /**
     * Save the User record, a fully populate user instance should be 
     * passed in and acted upon by the service. 
     *
     * @param \MentorApp\User user user instance
     * @return \MentorApp\User|null
     * @throws \PDOException
     */
    public function create(\MentorApp\User $user)
    {
        $fields = implode(', ', $this->mapping);
        $valueKeys = '';
        $statementValues = array();
        $user->id = $this->generate();
        foreach ($this->mapping as $key => $field) {
            $valueKeys .= ':' . $field . ', ';
            $statementValues[$field] = $user->$key;
        }
        $query = 'INSERT INTO user (' . $fields . ') VALUES (' . substr($valueKeys, 0, -2) . ')';

        $statement = $this->db->prepare($query);
        $statement->execute($statementValues);
        $this->saveSkills($user->id, $user->teachingSkills, self::SKILL_TYPE_TEACHING);
        $this->saveSkills($user->id, $user->learningSkills, self::SKILL_TYPE_LEARNING);
        return $user;
    }

    /**
     * Update method which will update the information for a user profile,
     * this will allow for a user to update their information should their
     * email, twitter, github handle, irc handle change or they want to 
     * start/stop mentoring or apprenticing
     *
     * @param \MentorApp\User user a user object with the properties set
     * @return boolean if the update is successful true returned, otherwise false
     * @throws \PDOException
     */
    public function update(\MentorApp\User $user)
    {
        $updateConditions = '';
        $updateValues = array();
        $mapping = $this->mapping;
        foreach ($mapping as $property => $field) {
            $updateConditions .= $field . '=:' . $field . ', ';
            $updateValues[$field] = $user->$property;
        }
        $updateQuery = 'UPDATE user SET ' . substr($updateConditions, 0, -2);
        $updateQuery .= ' WHERE id=:id';
        $statement = $this->db->prepare($updateQuery);
        $statement->execute($updateValues);
        $rowCount = $statement->rowCount();
        $this->deleteSkills($user->id);
        $this->saveSkills($user->id, $user->teachingSkills, self::SKILL_TYPE_TEACHING);
        $this->saveSkills($user->id, $user->learningSkills, self::SKILL_TYPE_LEARNING);
        if ($rowCount < 1) {
            return false;
        }
        return true;
    }

    /**
     * Delete the user data from the data store
     *
     * @param string id id of the user to be deleted
     * @return boolean
     * @throws \PDOException
     */
    public function delete($id)
    {
        $deleteQuery = "DELETE FROM user WHERE id = :id";
        $statement = $this->db->prepare($deleteQuery);
        $statement->execute(array('id' => $id));

        if ($statement->rowCount() < 1) {
            return false;
        }
        $this->deleteSkills($id);
        return true;
    }

    /**
     * Method to handle the saving of skills to a specific user
     *
     * @param string user_id id of the user
     * @param array skills an array of skill instances to attach to the user
     * @param string type the type of skills to be saved 
     * @throws \PDOException
     */
    private function saveSkills($user_id, array $skills, $type)
    {
        if (!$this->validSkillsType($type)) {
            return false;
        }
        $query = "INSERT INTO {$type}_skills (id_user, id_tag) VALUES (:user, :tag)";
        $statement = $this->db->prepare($query);
        foreach ($skills as $skill) {
            $statement->execute(array('user' => $user_id, 'tag' => $skill->id));
        }
    }

    /**
     * Method to remove all the skills associated to a user, identified by id
     * from the data stores
     * @param string id id of the user that the skills will be removed from
     * @return boolean returns true if skills were deleted successfully, false otherwise
     * @throws \PDOException
     */
    private function deleteSkills($id)
    {
        $teachingQuery = "DELETE FROM teaching_skills WHERE id_user = :id";
        $learningQuery = "DELETE FROM learning_skills WHERE id_user = :id";
        $teachingStatement = $this->db->prepare($teachingQuery);
        $learningStatement = $this->db->prepare($learningQuery);
        $teachingStatement->execute(array('id' => $id));
        $learningStatement->execute(array('id' => $id));
        return true;
    }

    /**
     * Validation method for skills types
     *
     * @param string the type to validate
     * @return boolean whether the string passed in is a valid skills type or not
     */
    private function validSkillsType($type)
    {
        return in_array($type, array(
            self::SKILL_TYPE_TEACHING,
            self::SKILL_TYPE_LEARNING
        ));
    }

    /**
     * Private method to get a list of all the skills saved in the table for
     * the user so items aren't double saved
     *
     * @param string user_id id of the user to look up
     * @param string type the type of skills to be retrieved
     * @return array an array of all the skill ids saved for the user
     * @throws \PDOException
     */
    private function retrieveSkills($user_id, $type)
    {
        if (!$this->validSkillsType($type)) {
            return false;
        }

        $statement = $this->db->prepare(
            "SELECT id_tag FROM {$type}_skills WHERE id_user = :id"
        );
        $statement->execute(array('id' => $user_id));

        $skills = array();
        while ($row = $statement->fetch()) {
            $skills[] = $row['id_tag'];
        }
            return $skills;
    }

    /**
     * Method to override the default mapping array
     *
     * @var array mapping mapping array
     */
    public function setMapping(Array $mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * Method to retrieve the mapping array
     *
     * @return array an array of the mappings
     */
    public function getMapping()
    {
        return $this->mapping;
    }

    /**
     * Implementation of abstract Hash::exists() used to make sure
     * the generated ID isn't already used for a user
     *
     * @param string $id the id that is being checked
     * @return boolean true if ID exists, false otherwise
     */
    public function exists($id)
    {
        if ($id === '' || !$this->validateHash($id)) {
            throw new \RuntimeException('Oh noes! Something went wrong and we weren\'t able to fix it');
        }
        try {
            $query = "SELECT id FROM `user` WHERE id = :id";
            $statement = $this->db->prepare($query);
            $statement->execute(['id' => $id]);
            if($statement->rowCount() > 0) {
                return true;
            }
        } catch (\PDOException $e) {
            // log it
            throw new \RuntimeException('Oh noes! Something went wrong and we weren\'t able to fix it');
        }
        return false;
    }

    /**
     * Method to retrieve a paginated list of users for the whole application
     *
     * @param int $results_per_page the number of results per page
     * @param int $page the page number to be retrieved
     * @return array an array of all the users on that page.
     * @throws \PDOException
     */
    public function retrieveAll($page=1, $results_per_page=20)
    {
        if (!is_int($results_per_page) || !is_int($page)) {
            throw new \RuntimeException('Something went wrong, we couldn\'t retrieve the users');
        }
        $fields = implode(', ', $this->mapping);
        $offset = ($page - 1) * $results_per_page;
        $userCollection = array(); 
        $query = "SELECT $fields FROM `user` ORDER BY `id` LIMIT $offset, $results_per_page";
        $statement = $this->db->prepare($query);
        $statement->execute();
        $users = $statement->fetchAll();
        foreach ($users as $user_result) {
            $userObject = new User();
            $user = $this->mapDatabaseResultToUser($userObject, $user_result);
            $user->teachingSkills = $this->retrieveSkills($user->id, self::SKILL_TYPE_TEACHING);
            $user->learningSkills = $this->retrieveSkills($user->id, self::SKILL_TYPE_LEARNING);
            $userCollection[] = $user;
        }    
        return $userCollection;
    } 

    /**
     * Protected method map the database results to the user object
     *
     * @param \MentorApp\User $user an empty user object
     * @param array $database_result a database result
     * @return \MentorApp\User a populated user object
     */
    protected function mapDatabaseResultToUser(\MentorApp\User $user, array $database_result)
    {
        foreach($this->mapping as $key => $value) {
            $user->$key = (isset($database_result[$value])) ? $database_result[$value] : null;
        }
        return $user;
    }

    /**
     * Method to search for users by the types of skills of they claim
     *
     * @param $skill string the name of the skill
     * @param $skillType string to indicate whether the skill is taught/learned
     * @return array an array of the user objects that have that skill
     */
    public function searchBySkill($skill, $skillType)
    {
        if ($skillType !== self::SKILL_TYPE_TEACHING && $skillType !== self::SKILL_TYPE_LEARNING) {
            throw new \InvalidArgumentException("The skill type you are trying to search by does not exist");
        }

        $skillTable = 'teaching_skills';
        if ($skillType === self::SKILL_TYPE_LEARNING) {
            $skillTable = 'learning_skills';
        }
        $query = 'SELECT u.id as user_id from `user` as u ';
        $query .= 'INNER JOIN ' . $skillTable . ' as st on u.id = st.id_user ';
        $query .= 'INNER JOIN `skill` as s on s.id = st.id_tag ';
        $query .= 'WHERE s.name like :skill';
        $statement = $this->db->prepare($query);
        $statement->execute(['skill' => strtolower($skill)]);
        $users = [];
        while (($row = $statement->fetch()) !== false) {
            $user = $this->retrieve($row['user_id']);
            $users[] = $user;
        }
        return $users;
    }

    /**
     * Method to retrieve a list of users by first name or last name
     *
     * @param $name string the name of the person you are searching for
     * @return array a list of all the users that match the query
     */
    public function searchByName($name)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException("You must use a string to search by name");
        }

        // if we get a first name and surname (ie: 'Joe Smith') - let's try to match it.
        $names = explode(' ', $name);
        $first_name = (isset($names[0])) ? $names[0] : '';
        $last_name = (isset($names[1])) ? $names[1] : '';

        $query = "SELECT `id` FROM `user` WHERE (`first_name` LIKE :name || `last_name` LIKE :name)";
        $query .= " || (`first_name` like :first_name && `last_name` like :last_name)";
        $statement = $this->db->prepare($query);
        $statement->execute(['name' => $name, 'first_name' => $first_name, 'last_name' => $last_name]);
        $users = [];
        while (($row = $statement->fetch()) !== false) {
            $user = $this->retrieve($row['id']);
            $users[] = $user;
        }
        return $users;
    }
}

