<?php
/**
 * @author Matt Frost <mfrost.design@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package MentorApp
 */

namespace MentorApp;

/**
 * User class that is used to represent the data pertaining to a 
 * single user
 */
class User
{
    /**
     * @var string id unique identifier for user
     */
    public $id;

    /**
     * @var string firstName first name of the user
     */
    public $firstName;

    /**
     * @var string lastName last name of the user
     */
    public $lastName;

    /**
     * @var string email email address for the user
     */
    public $email;

    /**
     * @var string github  github handle for the user
     */
    public $githubHandle;

    /**
     * @var int githubUid github user id
     */
    public $githubUid;

    /**
     * @var string ircNick irc nickname for the user
     */
    public $ircNick;

    /**
     * @var string twitterHandle twitter hanle for the user
     */
    public $twitterHandle;

    /**
     * @var int twitterUid twitter user id
     */
    public $twitterUid;

    /**
     * @var boolean mentorAvailable indicates whether user is accepting apprentices
     */
    public $mentorAvailable;

    /**
     * @var boolean apprenticeAvailable indicates whether user is seeking a mentor
     */
    public $apprenticeAvailable;

    /**
     * @var array teachingSkills an array of skills a user feels comfortable teaching
     */
    public $teachingSkills = array();

    /**
     * @var array learningSkills an array of skills a user wants to learn
     */
    public $learningSkills = array();

    /**
     * @var string timezone the time zone of the user
     */
    public $timezone;

    /**
     * Method to populate the array of teaching skills for the User
     *
     * @param \MentorApp\Skill A skill instance representing a single skill
     */
    public function addTeachingSkill(\MentorApp\Skill $skill)
    {
        $this->teachingSkills[] = $skill;
    }

    /**
     * Method to populate the array of learning skills for the User
     *
     * @param \MentorApp\Skill A skill instance representing a single skill
     */
    public function addLearningSkill(\MentorApp\Skill $skill)
    {
        $this->learningSkills[] = $skill;
    }
}
