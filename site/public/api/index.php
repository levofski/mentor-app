<?php
require_once('../../vendor/autoload.php');
$app = new \Slim\Slim();

// load in the config
require_once '../../config/environment.php';
require_once '../../config/database.php';

// setup constants for use in the endpoints
define("PARTNERSHIP_ROLE_MENTOR", "mentor");
define("PARTNERSHIP_ROLE_APPRENTICE", "apprentice");

// connect to the database
$app->db = new \PDO(
    'mysql:hostname='.$config['database'][$config['environment']]['hostname'].';dbname='.$config['database'][$config['environment']]['database'],
    $config['database'][$config['environment']]['username'],
    $config['database'][$config['environment']]['password']
);

$app->get('/v1/users/:id', function($id) use ($app) {
    // add authentication, authz shouldn't matter here
    $hashValidator = new \MentorApp\HashValidator();
    if (!$hashValidator->validate($id)) {
        $app->response->setStatus(404);
        return;
    }
    $response = array();
    $userService = new \MentorApp\UserService($app->db);
    $userResponse = $userService->retrieve($id);
    $skillService = new \MentorApp\SkillService($app->db);
    $partnershipManager = new \MentorApp\PartnershipManager($app->db);
    if ($userResponse === null) {
        $app->response->setStatus(404);
        return;
    }
    $userSerializer = new \MentorApp\UserArraySerializer();
    $skillSerializer = new \MentorApp\SkillArraySerializer();
    $partnershipSerializer = new \MentorApp\PartnershipArraySerializer();
    $response = $userSerializer->toArray($userResponse);

    // retrieve skill instances for the skill ids provided for teaching
    $learningSkills = $skillService->retrieveByIds($userResponse->learningSkills);
    $teachingSkills = $skillService->retrieveByIds($userResponse->teachingSkills);
    foreach ($learningSkills as $learningSkill) {
        $response['learningSkills'][] = $skillSerializer->toArray($learningSkill);
    }
    foreach ($teachingSkills as $teachingSkill) {
        $response['teachingSkills'][] = $skillSerializer->toArray($teachingSkill);
    }

    $mentorships = $partnershipManager->retrieveByMentor($id);
    $apprenticeships = $partnershipManager->retrieveByApprentice($id);
    $response['partnerships'] = array();
    $response['partnerships']['mentoring'] = $partnershipSerializer->fromArray($mentorships);
    $response['partnerships']['apprencting'] = $partnershipSerializer->fromArray($apprenticeships); 

    $app->response->setStatus(200);
    print json_encode($response); 
});

$app->delete('/v1/users/:id', function($id) use ($app) {
    $hashValidator = new \MentorApp\HashValidator();
    if (!$hashValidator->validate($id)) {
        $app->response->setStatus(404);
        return;
    }
    $userService = new \MentorApp\UserService($app->db);

    if (!$userService->delete($id)) {
        $app->response->setStatus(404);
        return;
    }
    $app->response->setStatus(200);
});

$app->post('/v1/users', function() use ($app) {
    $user = new \MentorApp\User();
    $userService = new \MentorApp\UserService($app->db);
    $skillService = new \MentorApp\SkillService($app->db);
    $data = $app->request->getBody();
    $dataArray = json_decode($data, true);
    $user->firstName = filter_var($dataArray['first_name'], FILTER_SANITIZE_STRING);
    $user->lastName = filter_var($dataArray['last_name'], FILTER_SANITIZE_STRING);
    $user->email = filter_var($dataArray['email'], FILTER_SANITIZE_EMAIL);
    $user->githubHandle = filter_var($dataArray['github_handle'], FILTER_SANITIZE_STRING);
    $user->twitterHandle = filter_var($dataArray['twitter_handle'], FILTER_SANITIZE_STRING);
    $user->ircNick = filter_var($dataArray['irc_nick'], FILTER_SANITIZE_STRING);
    $user->mentorAvailable = ($dataArray['mentor_available'] == 1) ? 1 : 0;
    $user->apprenticeAvailable = $dataArray['apprentice_available'] ? 1 : 0;
    $user->teachingSkills = array();
    $user->learningSkills = array();
    $user->timezone = filter_var($dataArray['timezone'], FILTER_SANITIZE_STRING);
    foreach ($dataArray['teaching_skills'] as $teaching) {
        $id = filter_var($teaching, '/^[0-9a-f]{10}$/');
        $user->teachingSkills[] = $skillService->retrieve($id);
    }

    foreach ($dataArray['learning_skills'] as $learning)
    {
        $id = filter_var($learning, '/^[0-9a-f]{10}$/');
        $user->learningSkills[] = $skillService->retrieve($id);
    } 

    $savedUser = $userService->create($user);
    if (!$savedUser) {
        $app->response->setStatus(400);
    }
    $app->response->setStatus(201);
});        

$app->put('/v1/users/:id', function() use ($app) {
    $user = new \MentorApp\User();
    $userService = new \MentorApp\UserService($app->db);
    $skillService = new \MentorApp\SkillService($app->db);
    $data = $app->request->getBody();
    $dataArray = json_decode($data, true);
    $user->id = filter_var($id, FILTER_SANITIZE_STRING);
    $user->firstName = filter_var($dataArray['first_name'], FILTER_SANITIZE_STRING);
    $user->lastName = filter_var($dataArray['last_name'], FILTER_SANITIZE_STRING);
    $user->email = filter_var($dataArray['email'], FILTER_SANITIZE_EMAIL);
    $user->githubHandle = filter_var($dataArray['github_handle'], FILTER_SANITIZE_STRING);
    $user->twitterHandle = filter_var($dataArray['twitter_handle'], FILTER_SANITIZE_STRING);
    $user->ircNick = filter_var($dataArray['irc_nick'], FILTER_SANITIZE_STRING);
    $user->mentorAvailable = ($dataArray['mentor_available'] == 1) ? 1 : 0;
    $user->apprenticeAvailable = $dataArray['apprentice_available'] ? 1 : 0;
    $user->teachingSkills = array();
    $user->learningSkills = array();
    $user->timezone = filter_var($dataArray['timezone'], FILTER_SANITIZE_STRING);
    foreach ($dataArray['teaching_skills'] as $teaching) {
        $id = filter_var($teaching, '/^[0-9a-f]{10}$/');
        $user->teachingSkills[] = $skillService->retrieve($id);
    }

    foreach ($dataArray['learning_skills'] as $learning)
    {
        $id = filter_var($learning, '/^[0-9a-f]{10}$/');
        $user->learningSkills[] = $skillService->retrieve($id);
    } 

    $savedUser = $userService->update($user);
    if (!$savedUser) {
        $app->response->setStatus(400);
    }
    $app->response->setStatus(200);
});

$app->get('/v1/skills', function() use ($app) {
    $skillService = new \MentorApp\SkillService($app->db);
    $skillSerializer = new \MentorApp\SkillArraySerializer();
    $userService = new \MentorApp\UserService($app->db);
    $userSerializer = new \MentorApp\UserArraySerializer();
    $users = $userService->retrieveAll();
    $response = array();
    // considered making the serializers able to take single instances
    // or an array of instances, in the meantime...this is going to have to do.
    foreach ($users as $user) {
        $serializedUser = $userSerializer->toArray($user);
        $learningSkills = $skillService->retrieveByIds($user->learningSkills);
        $teachingSkills = $skillService->retrieveByIds($user->teachingSkills);
        foreach ($learningSkills as $learn) {
            $serializedUser['learningSkills'][] = $skillSerializer->toArray($learn);
        }
        foreach ($teachingSkills as $teach) {
            $serializerUser['teachingSkills'][] = $skillSerializer->toArray($teach);
        }
        $response[] = $serializedUser;
    }
    $app-response->setStatus(200);
    print json_encode($response);
});

$app->get('/v1/skills/:id', function($id) use ($app) {
    $hashValidator = new \MentorApp\HashValidator();
    if (!$hashValidator->validate($id)) {
        $app->response->setStatus(404);
        return;
    }
    $skillService = new \MentorApp\SkillService($app->db);
    $skillSerializer = new \MentorApp\SkillArraySerializer();
    $skill = $skillService->retrieve($id);
    $skillArray = $skillSerializer->toArray($skill);
    if ($skill === null) {
        $app->response->setStatus(404);
        return;
    }
    $app->response->setStatus(200);
    print json_encode($skillArray);    
});

$app->delete('/v1/skills/:id', function($id) use ($app) {
    $hashValidator = new \MentorApp\HashValidator();
    if (!$hashValidator->validate($id)) {
        $app->response->setStatus(404);
        return;
    }
    $skillService = new \MentorApp\SkillService($app->db);
    if (!$skillService->delete($id)) {
        $app->response->setStatus(404);
        return;
    }
    $app->response->setStatus(200);
});

$app->post('/v1/skills', function() use ($app)  {
    $skillService = new \MentorApp\SkillService($app->db);
    $body = $app->request->getBody();
    $skillArray = json_decode($body, true);
    $skill = new \MentorApp\Skill();
    ($skillArray['name'] !== null) ? $skill->name = htmlspecialchars($skillArray['name']) : $skill->name = null;
    ($skillArray['added'] !== null) ? $skill->added = htmlspecialchars($skillArray['added']) : $skill->added = null;
    ($skillArray['authorized'] !== null) ? $skill->authorized = htmlspecialchars($skillArray['authorized']) : $skill->authorized = null;
    if (!$skillService->save($skill)) {
        $app->response->setStatus(400);
        return;
    }
    $app->response->setStatus(201);
});

$app->put('/v1/skills/:id', function() use ($app)   {
    $hashValidator = new \MentorApp\HashValidator();
    $skillService = new \MentorApp\SkillService($app->db);
    $body = $app->request->getBody();
    $skillArray = json_decode($body, true);
    $skill = new \MentorApp\Skill();
    ($skillArray['id'] !== null) ? $skill->id = htmlspecialchars($id) : $skill->id = null;
    ($skillArray['name'] !== null) ? $skill->name = htmlspecialchars($skillArray['name']) : $skill->name = null;
    ($skillArray['added'] !== null) ? $skill->added = htmlspecialchars($skillArray['added']) : $skill->added = null;
    ($skillArray['authorized'] !== null) ? $skill->authorized = htmlspecialchars($skillArray['authorized']) : $skill->authorized = null; 
    if (!$hashValidator->validate($skill->id)) {
        $app->response->setStatus(400);
        return;
    }
    if (!$skillService->save($skill)) {
        $app->response->setStatus(400);
        return;
    }
    $app->response->setStatus(200);
});

$app->get('/v1/partnerships/:id', function($id) use ($app) {
    $partnershipManager = new \MentorApp\PartnershipManager($app->db);
	$role = (!$app->request->get('role')) ? '' : $app->request->get('role');
	$partnerships = $partnershipManager->retrieveByRole($role, $id);

    if (empty($partnerships)) {
        $app->response->setStatus(404);
        return;
    }

    $output = array();

    $userService = new \MentorApp\UserService($app->db);
    $partnershipSerializer = new \MentorApp\PartnershipArraySerializer();
    $userSerializer = new \MentorApp\UserArraySerializer();
    $output = array();
    foreach ($partnerships as $partnership) {
        $mentor = $userService->retrieve($partnership->mentor);
        $partnership->mentor = $userSerializer->toArray($mentor);
        $apprentice = $userService->retrieve($partnership->apprentice);
        $partnership->apprentice = $userSerializer->toArray($apprentice);
        $output[] = $partnershipSerializer->toArray($partnership);
    }

    $app->response->setStatus(200);
    print json_encode($output);
});    

$app->post('/v1/partnerships', function() use ($app) {
    $requestData = $app->request->getBody();
    $data = json_decode($requestData, true);
    if (!isset($data['mentor']) || !isset($data['apprentice'])) {
        $app->response->setStatus(400);
        return;
    }

    $partnershipManager = new \MentorApp\PartnershipManager($app->db);
    $userService = new \MentorApp\UserService($app->db);
    $mentor = $userService->retrieve($data['mentor']);
    $apprentice = $userService->retrieve($data['apprentice']);

    if ($partnershipManager->create($mentor, $apprentice)) {
        $app->response->setStatus(201);
        return;
    }

    $app->setStatus(400);
});

$app->delete('/v1/partnerships/:id', function($id) use ($app) {
    $hashValidator = new \MentorApp\HashValidator();
    if (!$hashValidator->validate($id)) {
        $app->response->setStatus(404);
        return;
    }

    $partnershipManager = new \MentorApp\PartnershipManager($app->db);
    if ($partnershipManager->delete($id)) {
        $app->response->setStatus(200);
        return;
    }

    $app->response->setStatus(400);
});

$app->run();
