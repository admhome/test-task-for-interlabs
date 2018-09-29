<?php

/**
 * user class for manipulations with user
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
class User
{
    private $config;
    private $pdo;

    const TABLE = 'users';

    public function __construct($config)
    {
        $this->config = $config;
        $this->pdo = new SimplePDO($this->config['db']);
    }

    /**
     * Получить информацию о пользователе по ID
     * @param int $id
     * @return string
     */
    public function getUserById($id)
    {
        if (!empty($id)) {
            $query = 'SELECT `id`, `login`, `fio`, `email`, `address` FROM `' . self::TABLE . '` WHERE `id` = :id';
            $params = [
                ':id' => filter_var($id, FILTER_SANITIZE_NUMBER_INT),
            ];

            $result = $this->pdo->Query($query, $params);
        }

        return json_encode($result);
    }

    /**
     * Получить определённые поля из таблицы пользователей
     * @return string
     */
    public function getUsersSafe()
    {
        $query = 'SELECT `id`, `login`, `fio`, `email`, `address` FROM `' . self::TABLE . '`';
        $params = [
        ];

        $result = $this->pdo->Query($query, $params);

        return json_encode($result);
    }

    /**
     * Добавить пльзователя из ajax-запроса
     * @param array $userData
     * @return string
     */
    public function addUserByAjax($userData = [])
    {
        /*
        [
            'login' => $newUserLogin,
            'fio' => $newUserFIO,
            'email' => $newUserEmail,
            'address' => $newUserAddress,
        ]
         */
        if (!empty($userData)) {
            $query = 'INSERT INTO `' . self::TABLE . '` (`login`, `fio`, `email`, `address`) VALUES (:login, :fio, :email, :address);';
            $params = [
                ':login' => filter_var($userData['login'], FILTER_SANITIZE_STRING),
                ':fio' => filter_var($userData['fio'], FILTER_SANITIZE_STRING),
                ':email' => filter_var($userData['email'], FILTER_SANITIZE_STRING),
                ':address' => filter_var($userData['address'], FILTER_SANITIZE_STRING),
            ];

            $result = $this->pdo->Query($query, $params);
        }

        return $this->getUsersSafe();
    }

    public function changeUserByAjax($userData)
    {
        /*
         [
            'id' => $changedUserId,
            'login' => $changedUserLogin,
            'fio' => $changedUserFIO,
            'email' => $changedUserEmail,
            'address' => $changedUserAddress,
         ]
         */
        if (!empty($userData)) {
            $query = 'UPDATE `' . self::TABLE . '` SET `login` = :login, `fio` = :fio, `email` = :email, `address` = :address WHERE `id` = :id LIMIT 1;';
            $params = [
                ':login' => filter_var($userData['login'], FILTER_SANITIZE_STRING),
                ':fio' => filter_var($userData['fio'], FILTER_SANITIZE_STRING),
                ':email' => filter_var($userData['email'], FILTER_SANITIZE_STRING),
                ':address' => filter_var($userData['address'], FILTER_SANITIZE_STRING),

                ':id' => filter_var($userData['id'], FILTER_SANITIZE_NUMBER_INT),
            ];

            $result = $this->pdo->Query($query, $params);
        }

        return $this->getUsersSafe();
    }

    /**
     * Удалить пользователей по их ID
     * @param array $ids
     * @return string
     */
    public function delUsersByIds($ids)
    {
        if (!empty($ids)) {
            $query = 'DELETE FROM `' . self::TABLE . '` WHERE `id` IN (:ids);';
            $params = [
                ':ids' => implode(',', array_keys($ids)),
            ];

            $result = $this->pdo->Query($query, $params);
        }

        return $this->getUsersSafe();
    }

    //
}