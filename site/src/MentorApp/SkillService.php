<?php
/**
 * @author Stevan Goode <stevan@stevangoode.com>
 * @licence http://opensource.org/licences/MIT MIT
 * @package MentorApp
 */

namespace MentorApp;

/**
 * Class to interface with the data store and perform necessary actions with Skill objects
 */
class SkillService
{
    /**
     * Use the Hash trait to take care of hash generation
     */
    use Hash;

    /**
     * @var \PDO $db PDO instance of the data store connection
     */
    protected $db;

    /**
     * The standard constructor
     *
     * @param \PDO $db The data store connection
     */
    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Fetches a skill from the data store
     *
     * @param string $id Id of the skill to be retrieved
     * @return \MentorApp\Skill The retrieved Skill
     * @throws \InvalidArgumentException
     * @throws \PDOException
     */
    public function retrieve($id)
    {
        if (empty($id)) {
            throw new \InvalidArgumentException('ID cannot be empty');
        }

        $query = 'SELECT * FROM `skill` WHERE `id` = :id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);

        if (!$stmt->rowCount()) {
            return null;
        }

        $fields = $stmt->fetch(\PDO::FETCH_ASSOC);

        $skill = new Skill();
        $skill->id = $fields['id'];
        $skill->name = $fields['name'];
        $skill->added = new \DateTime($fields['added']);
        $skill->authorized = ($fields['authorized'] == 1);
        return $skill;
    }

    /**
     * Method to retrieve an array of skills from an array of IDs that are passed in
     *
     * @param array $ids an array of the ids corresponding to skills
     * @throws \PDOException
     * @return array an array of skill objects
     */
    public function retrieveByIds(Array $ids)
    {
        $skills = array();
        $inValue = '\'' . implode("', '", $ids) . '\'';
        $query = "SELECT * FROM `skills` WHERE `id` IN (:in)";
        $statement = $this->db->prepare($query);
        $statement->execute(['in' => $inValue]);
        while($row = $statement->fetch()) {
            $skill = new Skill();
            $skill->id = $row['id'];
            $skill->name = $row['name'];
            $skill->authorized = $row['authorized'];
            $skill->added = $row['added'];
            $skills[] = $skill;
        }
        return $skills;
    }

    /**
     * Searches for a skill based on a partial textual match
     *
     * @param string $term The term to search for
     * @return array The matching skill
     * @throws \InvalidArgumentException
     * @throws \PDOException
     */
    public function searchByTerm($term)
    {
        if (empty($term)) {
            throw new \InvalidArgumentException('No search term supplied');
        }

        $query = 'SELECT * FROM `skill` WHERE `name` LIKE "%:term%"';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':term' => $term]);
        if (!$stmt->rowCount()) {
            return [];
        }
        $return = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $skill = new Skill();
            $skill->id = $row['id'];
            $skill->name = $row['name'];
            $skill->added = new \DateTime($row['added']);
            $skill->authorized = ($row['authorized'] == 1);

            $skills[] = $skill;
        }
        return $skills;
    }

    /**
     * Saves a skill to the database
     *
     * @param \MentorApp\Skill $skill The skill to save
     * @return boolean
     * @throws \InvalidArgumentException
     * @throws \PDOException
     */
    public function save(Skill $skill)
    {
        if (empty($skill->name)) {
            throw new \InvalidArgumentException('Skill is missing a name');
        }
        if ($skill->id === null) {
            $skill->id = $this->generate();
        }
        $id = $skill->id;
        $name = $skill->name;
        $authorized = $skill->authorized ? 1 : 0;
        $added = $skill->added;
        $query = 'INSERT INTO `skill` (
            `id`,
            `name`,
            `authorized`,
            `added`
        ) VALUES (
            :id,
            :name,
            :authorized,
            :added
        ) ON DUPLICATE KEY UPDATE
            `authorized` = :authorized
        ';

        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $id, 'name' => $name, 'authorized' => $authorized, 'added' => $added]);
        return true;
    }

    /**
     * Method to delete an existing skill
     *
     * @param string $id the ID of the skill
     * @throws \PDOException
     * @return boolean
     */
    public function delete($id)
    {
        if (!filter_var($id, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' =>'/^[a-f0-9]{10}$/']])) {
            return false;
        }
        $query = "DELETE FROM `skill` WHERE id = :id";
        $statement = $this->db->prepare($query);
        $statement->execute(array('id' => $id));
        $rowCount = $statement->rowCount();
        if ($rowCount < 1) {
            return false;
        }
        return true;
    }

    /**
     * Exists method satisfies the contract of the trait and determines
     * whether or not the generated id exists in the system
     *
     * @param string id the id to check
     * @throws \PDOException
     * @return boolean returns true if id exists and false otherwise
     */
    public function exists($id)
    {
        $query = "SELECT id FROM `skill` WHERE id = :id";
        $statement = $this->db->prepare($query);
        $statement->execute(['id' => $id]);
        if($statement->rowCount() > 0) {
            return true;
        }
        return false;
    }
}
