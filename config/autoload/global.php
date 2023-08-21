<?php

return [
    'db' => [
        'driver' => 'oci8',
        'connection_string' => '(DESCRIPTION =
        (ADDRESS = (PROTOCOL = TCP)(HOST = 10.255.0.103)(PORT = 1521))
        (CONNECT_DATA =
        (SERVER = DEDICATED)
        (SERVICE_NAME = orcl)
        )
        )',
        'username' => 'JGI_AUG',
        'password' => 'JGI_AUG',
        'platform_options' => ['quote_identifiers' => false]
    ],
    'service_manager' => [
        'factories' => [
            'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
        ],
    ],
];
