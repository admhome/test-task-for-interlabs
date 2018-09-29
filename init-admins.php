<?php

die('<pre>Already did!</pre>');

require_once 'inc/Auth.php';

$config = include 'inc/config.php';
$auth = new Auth($config);

$admins = [
    [
        'login' => 'admin',
        'pass' => 'admin+12_',
    ],
    [
        'login' => 'aa',
        'pass' => 'qw12',
    ],
];

foreach ($admins as $k => $v) {
    $auth->initAdmin($v['login'], $v['pass']);
}