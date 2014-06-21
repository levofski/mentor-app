<?php
use fkooman\OAuth\Client\ClientConfig;

$clientConfig = array(
    'github' => new ClientConfig(
            array(
                "default_server_scope" => "user",
                "credentials_in_request_body" => true,
                "authorize_endpoint" => "https://github.com/login/oauth/authorize",
                "client_id" => "",
                "client_secret" => "",
                "token_endpoint" => "https://github.com/login/oauth/access_token"
            )
        )
    );