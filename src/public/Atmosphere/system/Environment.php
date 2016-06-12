<?php
return array(
    'version'     =>   '1.0.0',
    'filesystem'  =>   array (
        'framework'         =>  '&const:ATMOSPHERE',
        'workspace'         =>  '&fn:\Atmosphere\Atmosphere::workspace',
        'models'            =>  '&#:filesystem.workspace;models/',
        'applications'      =>  '&#:filesystem.workspace;applications/',
        'languages'         =>  '&#:filesystem.workspace;locale/',
        'logs'              =>  '&#:filesystem.workspace;logs/',
        'templates'         =>  '&#:filesystem.workspace;views/',
        'resources'         =>  '&#:filesystem.workspace;resources/',
        'scripts'           =>  '&#:filesystem.workspace;scripts/',
        'system'            =>  '&#:filesystem.workspace;system/',
        'cache'             =>  '&#:filesystem.workspace;cache/',
        'compile'           =>  '&#:filesystem.workspace;compile/',
        'libraries'         =>  '&#:filesystem.framework;libraries/',
        'frameworkSystem'   =>  '&#:filesystem.framework;system/',
        'workspaceSystem'   =>  '&#:filesystem.workspace;system/',
        'tmp'               =>  '&#:filesystem.workspace;tmp/',
        'root'              =>  '&const:ATMOSPHERE_ROOT',
    ),
    'rewrite'     =>   array(
        'ignoreExtension'   =>  true,
    ),
    'debug'       =>   array (
        'enable'            =>  true,
        'lines'             =>  5,
        'register'          =>  true,
        'format'            =>  'html',
        'displayErrors'     =>  false,
        'print'             =>  true,
        'embed'             =>  true,
        'linkToCss'         =>  '&#:filesystem.workspaceSystem;debug.css',
        'linkToJs'          =>  '&#:filesystem.workspaceSystem;debug.js',
    ),
    'cache'       =>   array (
        'enable'            =>  false,
        'prefix'            =>  '&fn:\Atmosphere\Rewrite::getHostname',
        'engine'            =>  'APC',
        'ttl'               =>  0,
    ),
    'session'     =>   array (
        'autoStart'         =>  true,
        'transid'           =>  false,
        'name'              =>  '', // use you own token like to 6ccl2MVu6HJnGMWricuTb38E8t4R5slhE8p
    ),
    'timezone'    =>   array (
        'default'           =>  '', // use you own timezone like to Chile/Continental
    ),
    'gettext'     =>   array (
        'enable'            =>  false,
        'defaultLanguage'   =>  'en_EN',
        'available'         =>  array (),
        'defaultDomain'     =>  'messages',
        'defaultDirectory'  =>  '&#:filesystem.languages;',
        'defaultCodeset'    =>  'UTF-8',
    ),
    'defaults'     =>   array (
        'controllers' => array (
            'methods' => array (
                'config' => '_config',
                'default' => 'index',
                'args' => NULL,
                'numArgs' => 0,
                'fillRemainingArgs' => true,
            ),
            'controller' => array (
                'file' => 'index.php',
                'class' => '\Atmosphere\Controller\index',
            )
        ),
        'applications' => array (
            'folder' => 'index',
        ),
        'http' => array (
            'responseCode' => 404,
        ),
        'timelimit' => array (
            'database' => 10,
        ),
        'tmp' => array (
            'default' => '&#:filesystem.tmp;',
        ),
    ),
    'databases'   =>   array ( // You can put multiple instances
        
        'localhost' => array (
            'hostname' => '',
            'username' => '',
            'password' => '',
            'database' => '', // set default database
            'service' => 'mysqli', // use mysqli, mysql, postgresql, sqlite, etc.
            'options' => array ( // set aditional options
                'MYSQLI_OPT_CONNECT_TIMEOUT' => 10,
            ),
        ),
        
    ),
    'handlers'    =>   array (
        'error' => array (
            array (
                0 => '\Atmosphere\Debug',
                1 => 'errorHandler',
            ),
        ),
        'shutdown' => array (
            array (
                '\Atmosphere\Debug',
                'shutdownHandler',
            ),
        ),
        'exception' => array (
            array (
                '\Atmosphere\Debug',
                'exceptionHandler',
            ),
        ),
    ),
    'routes'      =>   array (
        array(
            'description'   =>  '',
            'regexp'        =>  '#^demo/([a-z0-9]+)#i',
            'call'          =>  'index/hi/$1',
            'disableUrl'    =>    '',
            'redirectTo'    =>    '',
        ),
    ),
    'url'         =>   array (
        'scheme' => '&fn:\Atmosphere\Rewrite::getScheme',
        'host' => '&#:url.scheme;://&const:_SERVER_NAME_;',
    ),
    'core'          =>  array(
        'enabled'        =>  array(
            'typehint'      =>  true,
            'template'      =>  true,
            'debug'         =>  true,
            'routes'        =>  false,
        ),
        'controllerConf' => array (
            '&#:filesystem.workspaceSystem;settings.php',
        ),
    ),
);
