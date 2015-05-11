<?php

class db
{
    private $dbh;

    function __construct()
    {
        $dsn = 'mysql:dbname=reorg;host=127.0.0.1';
        $user = 'root';
        $password = '';

        try {
            $this->dbh = new PDO($dsn, $user, $password);
        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
        }
    }

    function exec($sql, array $params = [], $debug = FALSE)
    {
        $sth = $this->dbh->prepare($sql);

        foreach ($params as $k => &$v)
            $sth->bindParam($k, $v);

        return $sth->execute();
    }

    function last_insert_id()
    {
        return $this->dbh->lastInsertId();
    }

    function select($sql, array $params = [])
    {
        $sth = $this->dbh->prepare($sql);

        foreach ($params as $k => &$v)
            $sth->bindParam($k, $v);

        $sth->execute();

        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }
}

$db = new db();
?>