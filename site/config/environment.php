<?php

/**
 * @author  Stevan Goode <stevan@stevangoode.com>
 * @licence http://opensource.org/licences/MIT MIT
 */

// Defines the environment for the system to use - assumes production if APP_ENV is not set
$config['environment'] = getenv('APP_ENV') ?: 'production';

// Defines the hostname
$config['hostname'] = ($config['environment'] == 'dev') ? 'http://mentorapp.dev' : 'http://phpmentoring.org';