<?php

session_start();

require_once 'inc/Auth.php';
require_once 'inc/Template.php';

$config = include 'inc/config.php';
$auth = new Auth($config);
$tpl = new Template($config);

// auth
if (!empty($_POST['login']) && !empty($_POST['password'])) {
    $auth->authorize();
}

if ($auth->checkAuth()) {
    $template = 'index.tpl';
} else {
    $template = 'login.tpl';
}

$tpl->define_var('SITE_TITLE', $_SERVER['HTTP_HOST']);

//echo '<pre>[DEBUG] $tpl->Dump(): ' . var_export($tpl->Dump(), true) . '</pre>';
//die('<pre>File: ' . basename(__FILE__) . ', line: ' . __LINE__ . '</pre>');

$tpl->parse('header.tpl');
$tpl->parse($template);
$tpl->parse('footer.tpl');