<?php

require_once 'SimplePDO.php';

/**
 * class for check auth
 *
 * (PDO is in SimplePDO)
 *
 * `users` (
 *      `id` int(10)
 *      `login` varchar(255)
 *      `pass` varchar(255)
 *      `current_session` varchar(255)
 *      `fio` varchar(255)
 *      `email` varchar(255)
 *      `address` varchar(255)
 *  )
 */
class Auth
{
    private $config;

    private $pdo;

    private $isAuth;

    const TABLE = 'users';

    const SESS_LOGIN_NAME = 'interlabs_login';
    const SESS_KEY_NAME = 'interlabs_key';

    protected function encryptPassword($pass)
    {
        return password_hash($pass, PASSWORD_DEFAULT);
    }

    protected function verifyPassword($pass, $hash)
    {
        return password_verify($pass, $hash);
    }

    protected function getSessionHash()
    {
        return hash('sha1', time());
    }

    public function __construct($config)
    {
        $this->config = $config;
        $this->pdo = new SimplePDO($this->config['db']);
    }

    public function isAuth()
    {
        //
    }

    public function checkPassword($pass, $hash)
    {
        return password_hash(filter_var($pass, FILTER_SANITIZE_STRING), PASSWORD_DEFAULT) == $hash;
    }

    public function checkAuth()
    {
        if (empty($_SESSION[self::SESS_LOGIN_NAME]) || empty($_SESSION[self::SESS_KEY_NAME])) {
            return false;
        }

        $query = 'SELECT * FROM `' . self::TABLE . '` WHERE `login` = :login AND `current_session` = :current_session';
        $params = [
            ':login' => filter_var($_SESSION[self::SESS_LOGIN_NAME], FILTER_SANITIZE_STRING),
            ':current_session' => filter_var($_SESSION[self::SESS_KEY_NAME], FILTER_SANITIZE_STRING),
        ];

//        echo '<pre style="white-space: pre-wrap;">Query: ' . $query . '<br>With params: ' . var_export($params, true) . '<br>In file: ' . basename(__FILE__) . ' on line: ' . __LINE__ . '</pre>';

        return !empty($this->pdo->Query($query, $params));
    }

    public function authorize()
    {
        $login = filter_input(INPUT_POST, 'login', FILTER_SANITIZE_STRING);
        $pass = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

        $query = 'SELECT * FROM `' . self::TABLE . '` WHERE `login` = :login LIMIT 1';
        $params = [
            ':login' => $login,
        ];

//        echo '<pre style="white-space: pre-wrap;">Query: ' . $query . '<br>With params: ' . var_export($params, true) . '<br>In file: ' . basename(__FILE__) . ' on line: ' . __LINE__ . '</pre>';

        $result = $this->pdo->Query($query, $params);

        if (!empty($result) && $this->verifyPassword($pass, $result[0]['pass']) == true) {
            $_SESSION[self::SESS_LOGIN_NAME] = $result[0]['login'];
            $_SESSION[self::SESS_KEY_NAME] = $this->getSessionHash();

            $query = 'UPDATE `' . self::TABLE . '` SET `current_session` = :current_session WHERE `login` = :login LIMIT 1';
            $params = [
                ':current_session' => $_SESSION[self::SESS_KEY_NAME],
                ':login' => $login,
            ];

            return !empty($this->pdo->Query($query, $params));
        } else {
            if (!empty($_SESSION[self::SESS_LOGIN_NAME])) {
                unset($_SESSION[self::SESS_LOGIN_NAME]);
            }
            if (!empty($_SESSION[self::SESS_KEY_NAME])) {
                unset($_SESSION[self::SESS_KEY_NAME]);
            }

            return false;
        }
    }

    public function initAdmin($login, $pass)
    {
        $query = 'INSERT INTO `' . self::TABLE . '` (`login`, `pass`) VALUES (:login, :password) ON DUPLICATE KEY UPDATE `pass` = :password;';
        $params = [
            ':login' => filter_var($login, FILTER_SANITIZE_STRING),
            ':password' => $this->encryptPassword(filter_var($pass, FILTER_SANITIZE_STRING)),
        ];

        $result = $this->pdo->Query($query, $params);

        echo '<pre>$result: ' . var_export($result, true) . '</pre>';
    }
}