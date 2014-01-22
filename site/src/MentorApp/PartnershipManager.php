<?php
/**
 * @author Matt Frost <mfrost.design@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package MentorApp
 */

namespace MentorApp;

/**
 * Entity class to handle the relationship of a mentor and an apprentice
 */
class PartnershipManager
{
    /**
     * Use the hash trait to generate the id
     */
    use Hash;

    /**
     * Constants for the roles
     */
    const PARTNERSHIP_ROLE_MENTOR = 'mentor';
    const PARTNERSHIP_ROLE_APPRENTICE = 'apprentice';

    /** 
     * @var \PDO $db instance of PDO
     */
    protected $db;

    /**
     * Constructor
     *
     * @param \PDO $db
     */
    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Create a partnership between a mentor and an apprentice
     *
     * @param \MentorApp\User $mentor mentoring user instance
     * @param \MentorApp\User $apprentice apprentice user instance
     * @return boolean
     */
    public function create(User $mentor, User $apprentice)
    {
        $id = $this->generate();
        try {
            $query = "INSERT INTO partnerships (id, id_mentor, id_apprentice) VALUES (:id, :mentor, :apprentice)";
            $query .= " ON DUPLICATE KEY UPDATE mentor_id = :mentor";
            $statement = $this->db->prepare($query);
            $statement->execute(['id' => $id, 'mentor' => $mentor->id, 'apprentice' => $apprentice->id]);
            $rowCount = $statement->rowCount();
            if ($rowCount < 1) {
                return false;
            }
        } catch(\PDOException $e) {
            // log it
            return false;
        }
        return true;
    }

    /**
     * Removes a partnership
     *
     * @param string $id id of the relationship to delete
     * @return boolean
     */
    public function delete($id)
    {
        try {
            $query = "DELETE FROM partnership WHERE id = :id";
            $statement = $this->db->prepare($query);
            $statement->execute(['id' => $id]);
            $rowCount = $statement->rowCount();
            if ($rowCount < 1) {
                return false;
            }
        } catch(\PDOException $e) {
            // log it... 
            return false;
        }
        return true;
    }

    /**
     * Retrieves the information about a partnership by id
     *
     * @param string $id identifier for the partnership
     * @return \MentorApp\Partnership
     */
    public function retrieveById($id)
    {
        if (!$this->validateHash($id)) { 
            return null;
        }

        try {
            $query = "SELECT * FROM partnership WHERE id = :id";
            $statement = $this->db->prepare($query);
            $statement->execute(['id' => $id]);
            $row = $statement->fetch();
            $partnership = new Partnership();
            $partnership->id = $row['id'];
            $partnership->mentor = $row['id_mentor'];
            $partnership->apprentice = $row['id_apprentice'];
            return $partnership;
        } catch(\PDOException $e) {
            // log it
            return null;
        }
    }

    /**
     * Retrieve a partnership searching for matching records where the provided
     * id matches a mentor
     *
     * @param string $mentorId
     * @return array an array of Partnership instances
     */
    public function retrieveByMentor($mentorId)
    {
        if (!$this->validateHash($mentorId)) {
            return [];
        }

        $partnerships = [];
        try {
            $query = "SELECT * FROM `partnership` WHERE id_mentor = :mentor_id";
            $statement = $this->db->prepare($query);
            $statement->execute(['mentor_id' => $mentorId]);
            while ($row = $statement->fetch()) {
                $partnership = new Partnership();
                $partnership->id = $row['id'];
                $partnership->mentor = $row['id_mentor'];
                $partnership->apprentice = $row['id_apprentice'];
                $partnerships[] = $partnership;
            }
        } catch (\PDOException $e) {
            // log it
        }
        return $partnerships;
    }

    /**
     * Retrieve a group partnerships searching by apprentice
     *
     * @param string $apprenticeId
     * @return array
     */
    public function retrieveByApprentice($apprenticeId)
    {
        if (!$this->validateHash($apprenticeId)) {
            return [];
        }

        $partnerships = [];
        try {
            $query = "SELECT * FROM `partnership` WHERE id_apprentice = :apprentice_id";
            $statement = $this->db->prepare($query);
            $statement->execute(['apprentice_id' => $apprenticeId]);
            while ($row = $statement->fetch())
            {
                $partnership = new Partnership();
                $partnership->id = $row['id'];
                $partnership->mentor = $row['id_mentor'];
                $partnership->apprentice = $row['id_apprentice'];
                $partnerships[] = $partnership;
            }
        } catch(\PDOException $e) {
            // log it
        }
        return $partnerships;
    } 

    /**
     * Retrieve by role method will take an argument to determine how to filter the
     * retrieval request.
     *
     * @param string $role
     * @param string $id
     * @return array
     */
    public function retrieveByRole($role, $id)
    {
        switch(strtolower($role)) {
            case PARTNERSHIP_ROLE_MENTOR:
                $partnerships = $this->retrieveByMentor($id);
            break;

            case PARTNERSHIP_ROLE_APPRENTICE:
                $partnerships = $this->retrieveByApprentice($id);
            break;

            default:
                $partnerships = $this->retrieve($id);
        }
        return $partnerships;
    }
  
    /**
     * Method to fulfill the abstract Hash trait method and verify the id
     * being generated doesn't already exist
     *
     * @param string $id ID to validate/verify
     * @return boolean true if id exists, false if it doesn't
     */
    public function exists($id)
    {
        if ($id === '' || !preg_match('/^[A-Fa-f0-9]{10}$/', $id)) {
            throw new \RuntimeException('Yeah...so, we had a problem we couldn\'t resolve, sorry!');
        }
        try {
            $query = "SELECT id FROM `partnership` WHERE id = :id";
            $statement = $this->db->prepare($query);
            $statement->execute(['id' => $id]);
            if ($statement->rowCount() > 0) {
                return true;
            }
        } catch (\PDOException $e) {
            // log it
            throw new \RuntimeException('Yeah...so, we had a problem we couldn\'t resolve, sorry!');
        }
        return false;
    }
}
