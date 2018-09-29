<?php

/**
 * ajax module
 */

session_start();

$config = include 'inc/config.php';
require_once 'inc/Auth.php';
require_once 'inc/User.php';

header('Content-type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

$auth = new Auth($config);

if (!$auth->checkAuth()) {
    echo json_encode([
        'success' => false,
        'reason' => 'Not authorized',
    ]);
    die();
}

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
$user = new User($config);

switch($action) {

    /*
     * action: getUsers
     * return data: json
     * return: all users or fail
     */
    case 'getUsers':
        $usersList = $user->getUsersSafe();

        echo $usersList;
        break;

    /*
     * action: addUser
     * return data: json
     * return: add new user or fail
     */
    case 'addUser':
        $newUserLogin = filter_input(INPUT_POST, 'new_user_login', FILTER_SANITIZE_STRING);
        $newUserFIO = filter_input(INPUT_POST, 'new_user_fio', FILTER_SANITIZE_STRING);
        $newUserEmail = filter_input(INPUT_POST, 'new_user_email', FILTER_SANITIZE_STRING);
        $newUserAddress = filter_input(INPUT_POST, 'new_user_address', FILTER_SANITIZE_STRING);

        $result = $user->addUserByAjax([
            'login' => $newUserLogin,
            'fio' => $newUserFIO,
            'email' => $newUserEmail,
            'address' => $newUserAddress,
        ]);

        echo json_encode([]);
        break;

    /*
     * action: delUsers
     * return data: json
     * return: delete selected user(s) or fail
     */
    case 'delUsers':
        $delData = filter_input(INPUT_POST, 'delData', FILTER_UNSAFE_RAW);
        $delDataArr = [];
        parse_str($delData, $delDataArr);

        $result = $user->delUsersByIds($delDataArr);

        echo json_encode([]);
        break;

    case 'changeUser':
        $changedUserId = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
        $changedUserLogin = filter_input(INPUT_POST, 'new_user_login', FILTER_SANITIZE_STRING);
        $changedUserFIO = filter_input(INPUT_POST, 'new_user_fio', FILTER_SANITIZE_STRING);
        $changedUserEmail = filter_input(INPUT_POST, 'new_user_email', FILTER_SANITIZE_STRING);
        $changedUserAddress = filter_input(INPUT_POST, 'new_user_address', FILTER_SANITIZE_STRING);

        $result = $user->changeUserByAjax([
            'id' => $changedUserId,
            'login' => $changedUserLogin,
            'fio' => $changedUserFIO,
            'email' => $changedUserEmail,
            'address' => $changedUserAddress,
        ]);

        echo json_encode([$result]);
        break;

    case 'getUserData':
        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

        echo $user->getUserById($id);
        break;

    default:
        echo json_encode([]);

};
