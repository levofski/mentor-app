<?php
require_once('../../vendor/autoload.php');
$app = new \Slim\Slim();

// load in the config
require_once('../../config/config.php');

// spin up sessions
$app->add(new \Slim\Middleware\SessionCookie($session));

// setup constants for use in the endpoints
define("PARTNERSHIP_ROLE_MENTOR", "mentor");
define("PARTNERSHIP_ROLE_APPRENTICE", "apprentice");

// connect to the database
$app->db = new \PDO(
    'mysql:hostname='.$config['database'][$config['environment']]['hostname'].';dbname='.$config['database'][$config['environment']]['database'],
    $config['database'][$config['environment']]['username'],
    $config['database'][$config['environment']]['password']
);

/**
 * Handles oauth based callbacks
 */
$app->get('/v1/oauth', function() use ($app, $clientConfig) {
    $auth = new \MentorApp\Auth($app);

    if ($auth->callbackOAuth($clientConfig[$_SESSION['oauth_session']['type']])) {
        $app->response->redirect('/doLogin/' . $_SESSION['oauth_session']['type'], 302);
    } else {
        $app->response->redirect('/', 302);
    }
});

/**
 * Handles oauth based authentication
 */
$app->get('/v1/oauth/:type', function($type) use ($app, $clientConfig) {
    $app->response->headers->set('Content-Type', 'application/json');

    $auth = new \MentorApp\Auth($app);
    $result = $auth->initialOAuth($type, array("user"), $clientConfig[$type]);

    if (is_string($result)) {
        $app->response->setBody(json_encode(array('redirect' => $result)));
    } elseif ($result) {
        $app->response->setBody(json_encode(array('redirect' => '/doLogin/' . $type)));
    } else {
        $app->response->setStatus(403);
    }
});

/**
 * Retrieves github profile information for the authorized (logged in) user
 */
$app->get('/v1/github/profile', function () use ($app) {
    $app->response->headers->set('Content-Type', 'application/json');

    $github = new \MentorApp\Github($app, new \MentorApp\Auth($app));

    if ($response = $github->getProfile()) {
        $app->response->setBody($response->getBody());
    } else {
        $app->response->setBody(json_encode(array('status' => 'error')));
    }
});

$app->get('/v1/users/:id', function($id) use ($app) {
    $response = array();
    $userService = new \MentorApp\UserService($app->db);

    // handles third party login requests for user profile
    if (isset($_GET['third_party'])) {
        switch ($_GET['third_party']) {
            case 'github':
                $id = $userService->getUserId('github', $id);
                break;
            case 'twitter':
                $id = $userService->getUserId('twitter', $id);
                break;
            default:
                $id = null;
        }
    }

    // add authentication, authz shouldn't matter here
    $hashValidator = new \MentorApp\HashValidator();
    if (!$hashValidator->validate($id)) {
        $app->response->setStatus(404);
        return;
    }

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

    $response['partnerships'] = [];
    $mentorships = $partnershipManager->retrieveByMentor($id);
    $apprenticeships = $partnershipManager->retrieveByApprentice($id);
    $response['partnerships']['mentoring'] = [];
    foreach ($mentorships as $mentorship) {
        $response['partnerships']['mentoring'][] = $partnershipSerializer->toArray($mentorship);
    }
    $response['partnerships']['apprenticing'] = [];
    foreach ($apprenticeships as $apprenticeship) {
        $response['partnerships']['apprenticing'] = $partnershipSerializer->toArray($apprenticeship); 
    }

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
    $user->githubUid = filter_var($dataArray['github_uid'], FILTER_SANITIZE_STRING);
    $user->twitterHandle = filter_var($dataArray['twitter_handle'], FILTER_SANITIZE_STRING);
    $user->twitterUid = filter_var($dataArray['twitter_uid'], FILTER_SANITIZE_STRING);
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

    foreach ($dataArray['learning_skills'] as $learning) {
        $id = filter_var($learning, '/^[0-9a-f]{10}$/');
        $user->learningSkills[] = $skillService->retrieve($id);
    }

    $savedUser = $userService->create($user);
    if (!$savedUser) {
        $app->response->setStatus(400);
    }
    $app->response->setStatus(201);
    $app->response->header('Location', '/api/v1/users/'.urlencode($user->id));
    $app->response->header('Content-Type', 'application/json');
    print json_encode(['id' => $user->id]);
});        

$app->put('/v1/users/:id', function($id) use ($app) {
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

$app->get('/v1/users', function() use ($app) {
    $skillService = new \MentorApp\SkillService($app->db);
    $skillSerializer = new \MentorApp\SkillArraySerializer();
    $userService = new \MentorApp\UserService($app->db);
    $userSerializer = new \MentorApp\UserArraySerializer();
    $users = $userService->retrieveAll();
    $response = array();
    foreach ($users as $user) {
        $learningSkills = $skillService->retrieveByIds($user->learningSkills);
        $teachingSkills = $skillService->retrieveByIds($user->teachingSkills);
        $serializedUser = $userSerializer->toArray($user);
        $serializedUser['learningSkills'] = [];
        $serializedUser['teachingSkills'] = [];
        foreach ($learningSkills as $learn) {
            $serializedUser['learningSkills'][] = $skillSerializer->toArray($learn);
        }
        foreach ($teachingSkills as $teach) {
            $serializedUser['teachingSkills'][] = $skillSerializer->toArray($teach);
        }
        $response[] = $serializedUser;
    }
    $app->response->setStatus(200);
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

$app->put('/v1/skills/:id', function($id) use ($app)   {
    $hashValidator = new \MentorApp\HashValidator();
    $skillService = new \MentorApp\SkillService($app->db);
    $body = $app->request->getBody();
    $skillArray = json_decode($body, true);
    $skill = new \MentorApp\Skill();
    ($id !== null) ? $skill->id = htmlspecialchars($id) : $skill->id = null;
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

    $app->response->setStatus(400);
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
