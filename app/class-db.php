<?php
class DB
{
    protected $connection;
    private static $instance;
    protected $host;
    protected $user;
    protected $password;
    protected $db_name;

    public static function getInstance()
    {
        if(self::$instance===null){
            self::$instance=new self;
        }
        return self::$instance;
    }
    private function __construct()
    {
        try {
            $this->connection = new \PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASSWORD);
            $this->connection->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
            $this->connection->query('SET NAMES UTF8');

        }catch(\PDOException $e) {
            echo $e->getMessage();die;
        }
    }

    private function __clone(){}
    public function query($sql)
    {
        if (!$this->connection) {
            return false;
        }
        $result = $this->connection->query($sql);
        
        if (is_bool($result)) {
            return $result;
        }
        $data = $result->fetchAll(\PDO::FETCH_ASSOC);

        return $data;
    }
    public function exec($sql)
    {
        if (!$this->connection) {
            return false;
        }
        return $this->connection->exec($sql);
    }

//    public function prepared_query($sql,array $data=null)
//    {
//        if (!$this->connection) {
//            return false;
//        }
//        $result=$this->connection->prepare($sql);
//        $result->execute($data);
//        $data = array();
//        $data = $result->fetchAll(\PDO::FETCH_ASSOC);
//
//        return $data;
//    }

    public function getConnection()
    {
        return $this->connection;
    }

}