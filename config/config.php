<?php
return [
    'db_host' => '127.0.0.1',
    'db_port' => '8889',
    'db_name' => 'vm_db',
    'db_user' => 'root',
    'db_pass' => 'root',

    // JWT (API login): secret key used to sign tokens. Keep it private!
    // Use a different random value on the live server.
    'jwt_secret' => '7b75f0b498906b00a585ee4442373c441dd578b3f9f09a9d3d3ae01a0ba17c80',
    'jwt_ttl'    => 3600, // token lifetime in seconds (1 hour)
];
