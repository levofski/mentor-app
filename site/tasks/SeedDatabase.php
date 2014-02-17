<?php
require_once __DIR__ . "/../vendor/autoload.php";
require_once "phing/Task.php";

class SeedDatabase extends Task
{
    private $numSkills;

    private $numUsers;
    
    public function setNumskills($num)
    {
        $this->numSkills = intval($num);
    }

    public function setNumusers($num)
    {
        $this->numUsers = intval($num);
    }
    public function init()
    {
       $this->faker = Faker\Factory::create(); 

       //hacky hacky hacky, keep the config hacky
       require_once __DIR__.'/..//config/database.php';
       $this->pdo = new \PDO(
           'mysql:hostname='.$config['database']['development']['hostname'].';dbname='.$config['database']['development']['database'],
           $config['database']['development']['username'],
           $config['database']['development']['password']
       );

       $this->skills = array();
       $this->mentorPool = array();
       $this->apprenticePool = array();
       $this->numSkills = 100;
       $this->numUsers = 500;
    }

    public function main()
    {
        $this->populateSkills();
        $this->populateUsers();
    }

    private function populateSkills()
    {
        $skillService = new \MentorApp\SkillService($this->pdo);
        for ($i = 0; $i < $this->numSkills; $i++) {
            $skill = new \MentorApp\Skill();
            $skill->name = $this->faker->word;
            $skill->authorized = $this->faker->boolean; 
            $skill->added = date('Y-m-d H:i:s');

            $skillService->save($skill);
            $this->skills[] = $skill;
        }
    }

    private function populateUsers()
    {
        $userService = new \MentorApp\UserService($this->pdo);
        for ($j = 0; $j < $this->numUsers; $j++) {
            $user = new \MentorApp\User();

            $userData = [
                'firstName' => $this->faker->firstName,
                'lastName' => $this->faker->lastName,
                'email' => $this->faker->email,
                'githubHandle' => $this->faker->firstName,
                'ircNick' => $this->faker->firstName,
                'twitterHandle' => $this->faker->firstName,
                'mentorAvailable' => $this->faker->boolean,
                'apprenticeAvailable' => $this->faker->boolean,
                'timezone' => $this->faker->timezone,
            ];

            foreach ($userData as $property => $value) {
                $user->$property = $value;
            }

            if ($user->mentorAvailable) {
                for ($i = 1, $numSkills = rand(1, 3); $i <= $numSkills; $i++) {
                    $skillIndex = rand(0, count($this->skills) - 1);
                    if ($skillIndex >= 0) {
                        $user->addTeachingSkill($this->skills[$skillIndex]);
                    }
                }
                $this->addToMentorPool($user);
            }

            if ($user->apprenticeAvailable) {
                for ($i = 1, $numSkills = rand(1, 3); $i <= $numSkills; $i++) {
                    $skillIndex = rand(0, count($this->skills) - 1);
                    if ($skillIndex >= 0) {
                        $user->addLearningSkill($this->skills[$skillIndex]);
                    }
                }
                $this->addToApprenticePool($user);
            }

            $userService->create($user);
            $this->populatePartnerships();
        }
    }

    private function addToMentorPool($user)
    {
        if (count($this->mentorPool) == 10) {
            array_shift($this->mentorPool);
        }

        $this->mentorPool[] = $user;
        shuffle($this->mentorPool);
    }

    private function addToApprenticePool($user)
    {
        if (count($this->apprenticePool) == 10) {
            array_shift($this->apprenticePool);
        }

        $this->apprenticePool[] = $user;
        shuffle($this->apprenticePool);
    }

    public function populatePartnerships()
    {
        $partnershipManager = new \MentorApp\PartnershipManager($this->pdo);
        for($i = 0; $i < 2; $i++) {
            $mentorKey = array_rand($this->mentorPool);
            $apprenticeKey = array_rand($this->apprenticePool);
            if (null === $mentorKey || $null === $apprenticeKey) {
                continue;
            }

            $partnershipManager->create(
                $this->mentorPool[$mentorKey],
                $this->apprenticePool[$apprenticeKey]
            ); 
        }
    }
}
