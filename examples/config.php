<?php
$path = dirname(__DIR__).DIRECTORY_SEPARATOR."src".
        DIRECTORY_SEPARATOR;

set_include_path(get_include_path()
        .PATH_SEPARATOR. $path);

require_once 'rkt/MessageAPI/Bootstrap.php';
rkt\MessageAPI\Bootstrap::init($path);