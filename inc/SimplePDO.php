<?php

/**
 * Class for easy PDO manipulations
 */
class SimplePDO
{
    const FETCH_MODE = PDO::FETCH_ASSOC;

    private $config;
    private $pdo;

    public function __construct($config)
    {
        if (empty($config)) {
            throw new Exception('Config can\'t be blank', 1);
        }
        $this->config = $config;
    }

    /**
     * Connect to mysql
     */
    protected function Connect()
    {
        try {
            $this->pdo = new PDO(
                'mysql:host=localhost;dbname=' . $this->config['db_name'],
                $this->config['login'],
                $this->config['pass'],
                [
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]
            );
        } catch (PDOException $e) {
            echo sprintf(
                '<pre style="white-space: pre-wrap;">[PDO ERROR] #%s - %s</pre>',
                $e->getCode(),
                $e->getMessage()
            );
            die('<pre>File: ' . basename(__FILE__) . ', line: ' . __LINE__ . '</pre>');
        }
    }

    /**
     * Disconnect from mysql
     */
    public function CloseConnection()
    {
        $this->pdo = null;
    }

    /**
     * Try exec a query
     * @param $query
     * @param array $params
     * @return result|affected rows|null
     */
    public function Query($query, $params = [])
    {
        if (empty($this->pdo)) {
            $this->Connect();
        }

//        echo '<pre style="white-space: pre-wrap;">Query: ' . $query . '<br>With params: ' . var_export($params, true) . '<br>In file: ' . basename(__FILE__) . ' on line: ' . __LINE__ . '</pre>';

        try {
            $stmt = $this->pdo->prepare($query);

            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }

            $queryType = strtolower(strtok($query, ' '));
            $stmt->execute();

            if (in_array($queryType, ['select', 'show'])) {
                return $stmt->fetchAll(self::FETCH_MODE);
            } elseif (in_array($queryType, ['insert', 'update', 'delete'])) {
                return $stmt->rowCount();
            } else {
                return NULL;
            }
        } catch (PDOException $e) {
//            echo '<pre style="white-space: pre-wrap;">PDOException #' . $e->getCode() . ' - ' . $e->getMessage() . '<br />' . PHP_EOL .
//                'With stack trace: <br>' . PHP_EOL . $e->getTraceAsString() . '</pre>';
//            die('<pre>File: ' . basename(__FILE__) . ', line: ' . __LINE__ . '</pre>');
            die(json_encode([
                'error' => 'Die in ' . basename(__FILE__) . ' on line: ' . __LINE__ . ' with PDO Exception ' . $e->getMessage(),
            ]));
        }
    }
}