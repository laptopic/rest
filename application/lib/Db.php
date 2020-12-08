<?php

namespace application\Lib;

use PDO;

class Db extends PDO
{
    protected $conn=false, $sql, $start, $errnum, $errmsg;

    public function __construct()
    {
        $config = require 'application/config/db.php';
        try {
            parent::__construct("mysql:host=" . $config['host'] . ";dbname=" .$config['name'], $config['user'], $config['password']);
            $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->exec("set names utf8");
            $this->clearQuery();
            $this->conn = true;
        }
        catch (PDOException $e) {
            die($e->getMessage());
            //$this->setError($e->getMessage());
        }
    }

    private function clearQuery() {
        $this->start = microtime(1);
        $this->sql = '';
        $this->errmsg = '';
        $this->errnum = 0;
    }

    private function setError($msg)
    {
        $this->errmsg = $msg ? $msg : $this->errorInfo();
        $this->errnum = $this->errorCode() or $this->errnum = -1;
        if ($_SESSION['debug']) {
            $stack = debug_backtrace();
            do { $call = array_shift($stack); } while ($call['file'] == __FILE__);
            if (@$_SERVER['REQUEST_URI']) debug_mes(1, $this->errmsg, $call['file'], $call['line'], $this->sql);
            else echo "$this->errmsg in $call[file] on $call[line]\n\tSQL: $this->sql\n";
        }
        return false;
    }

    private function debug()
    {
        if ($_SESSION['debug']<2 || strstr($_SERVER['SCRIPT_FILENAME'],'index.php')) return;
        $time = round(microtime(1) - $this->start,3);
        if ($_SESSION['debug']<3 && $time<0.1) return;
        $stack = debug_backtrace();
        do { $call = array_shift($stack); } while ($call['file'] == __FILE__);
        debug_mes(1,"<span style=\"color:green\">Query time: $time sec</span>", $call['file'], $call['line'], $this->sql);
    }

    public function error()
    {
        return $this->errmsg ? $this->errmsg : false;
    }

    public function query( $sql=null, $params=null )
    {
        if (!$this->conn) return false;
        $this->clearQuery();

        $params = func_get_args();
        if (!$params) { // if empty take parent function args
            $stack = debug_backtrace();
            $params = $stack[0]['file']==__FILE__ ? $stack[1]['args'] : null;
        }

        $this->sql = array_shift($params);
        preg_match('/^(\w+)\s/s',trim($this->sql),$qtype);
        $qtype = strtoupper($qtype[1]);

        preg_match_all('/(?:[\W]):([A-Za-z][A-Za-z_0-9]*)/', $this->sql, $var);
        $var = array_unique($var[1]);

        if (is_array(@$params[0])) {
            $params = $params[0];
            foreach ($var as $v) if (!array_key_exists($v,$params)) return $this->setError("Ошибка привязки параметра :$v");
            $var = array_fill_keys($var,1);
            foreach ($params as $k=>$v) if (!@$var[$k]) unset($params[$k]);
        } elseif ($var&&$params) {
            if (count($var)>count($params)) return $this->setError("Переданы не все указаные параметры");
            $params = array_combine($var,$params);
        }
        else $params = array();

        try {
            $stmt = $this->prepare($this->sql);
            $stmt->execute($params);
            if     ($qtype=='INSERT') $res = $this->lastInsertId();
            elseif ($qtype=='UPDATE') $res = $stmt->rowCount();
            elseif ($qtype=='DELETE') $res = $stmt->rowCount();
            else { $res = array(); while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) $res[] = $row; }
        }
        catch (PDOException $e) {
            return $this->setError( $e->getMessage() );
        }
        $this->debug();
        return $res;
    }

    public function value( $sql, $params=null )
    {
        $res = $this->query();
        if (!$res) return false;
        while (is_array($res)) $res = reset($res);
        return $res;
    }

    public function values( $sql, $params=null )
    {
        $res = $this->query();
        if (!$res) return $res;
        $val = array();
        foreach ($res as $r) $val[] = reset($r);
        return $val;
    }

    public function values_by_id( $sql, $params=null )
    {
        $res = $this->query();
        if (!$res) return $res;
        $val = array();
        foreach ($res as $r) {
            list($k, $v) = array_values($r);
            $val[$k] = $v;
        }
        return $val;
    }

    public function lst( $sql, $params=null )
    {
        $res = $this->query();
        if (!$res) return $res;
        return array_values(reset($res));
    }

    public function lsts( $sql, $params=null )
    {
        $res = $this->query();
        if (!$res) return $res;
        foreach ($res as $n=>$r) $res[$n] = array_values($r);
        return $res;
    }

    public function lsts_by_id( $sql, $params=null )
    {
        $res = $this->query();
        if (!$res) return $res;
        $val = array();
        foreach ($res as $r) {
            $id = array_shift($r);
            $val[$id] = array_values($r);
        }
        return $val;
    }

    public function row( $sql, $params=null )
    {
        $res = $this->query();
        if (!$res) return $res;
        return $res[0];
    }

    public function rows( $sql, $params=null )
    {
        $res = $this->query();
        if (!$res) return $res;
        return $res;
    }


    public function rows_by_id( $sql, $params=null )
    {
        $res = $this->query();
        if (!$res) return $res;
        $val = array();
        foreach ($res as $r) {
            $k = array_shift($r);
            $val[$k] = $r;
        }
        return $val;
    }
}
