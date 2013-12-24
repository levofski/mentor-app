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
     * data
     */
    public function retrieve($id)
    {
        if (!$this->validateHash($id)) {
            return null;
        }

        try {
            $query = 'SELECT ' . implode(', ', $this->mapping) . ' FROM user WHERE id = :id';
            $statement = $this->db->prepare($query);
            $statement->execute(array('id' => $id));
            $userData = $statement->fetch();
            if ($statement->rowCount() < 1) {
                return null;
            }
            $user = new User();
            foreach ($this->mapping as $property => $dbColumnName) {
                $user->$property = htmlentities($userData[$dbColumnName]);
            }

            $user->teachingSkills = $this->retrieveSkills($id, self::SKILL_TYPE_TEACHING);
            $user->learningSkills = $this->retrieveSkills($id, self::SKILL_TYPE_LEARNING);

            return $user;
        } catch (\PDOException $e) {
            // log the error
            return null;
        }
    }

    /**
     * Save the User record, a fully populate user instance should be 
     * passed in and acted upon by the service. 
     *
     * @param \MentorApp\User user user instance
     * @return boolean indication of whether the user was saved correctly
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

        try {
            $statement = $this->db->prepare($query);
            $statement->execute($statementValues);
            $this->saveSkills($user->id, $user->teachingSkills, self::SKILL_TYPE_TEACHING);
            $this->saveSkills($user->id, $user->learningSkills, self::SKILL_TYPE_LEARNING);
        } catch (\PDOException $e) {
            // log errors
            return false;
        }
        return true;
    }

    /**
     * Update method which will update the information for a user profile,
     * this will allow for a user to update their information should their
     * email, twitter, github handle, irc handle change or they want to 
     * start/stop mentoring or apprenticing
     *
     * @param \MentorApp\User user a user object with the properties set
     * @return boolean if the update is successful true returned, otherwise false
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
        try {
            $statement = $this->db->prepare($updateQuery);
            $statement->execute($updateValues);
            $rowCount = $statement->rowCount();
            $this->deleteSkills($user->id);
            $this->saveSkills($user->id, $user->teachingSkills, self::SKILL_TYPE_TEACHING);
            $this->saveSkills($user->id, $user->learningSkills, self::SKILL_TYPE_LEARNING);
        } catch (\PDOException $e) {
            // log
        }
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
     */
    public function delete($id)
    {
        $deleteQuery = "DELETE FROM user WHERE id = :id";
        try {
            $statement = $this->db->prepare($deleteQuery);
            $statement->execute(array('id' => $id));

            if ($statement->rowCount() < 1) {
                return false;
            }
            $this->deleteSkills($id);
        } catch (\PDOException $e) {
            // log it
        }
        return true;
    }

    /**
     * Method to handle the saving of skills to a specific user
     *
     * @param string user_id id of the user
     * @param array skills an array of skill instances to attach to the user
     * @param string type the type of skills to be saved 
     */
    private function saveSkills($user_id, array $skills, $type)
    {
        if (!$this->validSkillsType($type)) {
            return false;
        }
        $query = "INSERT INTO {$type}_skills (id_user, id_tag) VALUES (:user, :tag)";
        $statement = $this->db->prepare($query);
        foreach ($skills as $skill) {
            try {
                $statement->execute(array('user' => $user_id, 'tag' => $skill->id));
            } catch (\PDOException $e) {
                //TODO log it
                // maybe rethrow it and catch it in the create/update methods?
            }
        }
    }

    /**
     * Method to remove all the skills associated to a user, identified by id
     * from the data stores
     * @param string id id of the user that the skills will be removed from
     * @return boolean returns true if skills were deleted successfully, false otherwise
     */
    private function deleteSkills($id)
    {
        $teachingQuery = "DELETE FROM teaching_skills WHERE id_user = :id";
        $learningQuery = "DELETE FROM learning_skills WHERE id_user = :id";
        try {
            $teachingStatement = $this->db->prepare($teachingQuery);
            $learningStatement = $this->db->prepare($learningQuery);
            $teachingStatement->execute(array('id' => $id));
            $learningStatement->execute(array('id' => $id));
        } catch (\PDOException $e) {
            // log the error
            return false;
        }
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
     */
    private function retrieveSkills($user_id, $type)
    {
        if (!$this->validSkillsType($type)) {
            return false;
        }

        try {
            $statement = $this->db->prepare(
                "SELECT id_tag FROM {$type}_skills WHERE id_user = :id"
            );
            $statement->execute(array('id' => $user_id));

            $skills = array();
            while ($row = $statement->fetch()) {
                $skills[] = $row['id_tag'];
            }
            return $skills;
        } catch (\PDOException $e) {
            //TODO log it
            return array();
        }
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
            $query = "SELECT id FROM `users` WHERE id = :id";
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
}
