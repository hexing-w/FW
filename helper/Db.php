<?php

namespace FW\Helper;

class Db {
    private static $pdo = null;
    public static $statement = null;
    private static $is_addsla = false;
    public static $options = array(
        \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES ",
    );

    public  static function init($config = []){
        $config = MConfig::get('db');
        $host=$config['host'];
        $user=$config['user'];
        $pass=$config['pass'];
        $dbname=$config['dbname'];
        $persistent=$config['persistent'];
        $charset=$config['charset'];

        if(!strpos(self::$options[\PDO::MYSQL_ATTR_INIT_COMMAND], $charset))
        {
            self::$options[\PDO::MYSQL_ATTR_INIT_COMMAND] .= $charset;
        }
        if($persistent){
            self::$options[\PDO::ATTR_PERSISTENT] = true;
        }
        $dsn = "mysql:host={$host};dbname={$dbname}";
        self::$pdo = new \PDO($dsn,$user,$pass,self::$options);
    }
    /**
    全局属性设置，包括：列名格式和错误提示类型    可以使用数字也能直接使用参数
    */
    public  static function setAttr($param, $val=''){
        self::init();
        if(is_array($param)){
            foreach($param as $key=>$val){
                self::$pdo->setAttribute($key,$val);
            }
        }else{
            if($val!=''){
                self::$pdo->setAttribute($param,$val);
            }else{
                return false;
            }
             
        }
    }
    /**
    生成一个编译好的sql语句模版 你可以使用 ? :name 的形式
    返回一个statement对象
    */
    public  static function prepare($sql=""){
        self::init();
        if($sql==""){
            return false;
        }
        self::$statement = self::$pdo->prepare($sql);
        return self::$statement;
    }
    /**
    执行Sql语句，一般用于 增、删、更新或者设置  返回影响的行数
    */
    public  static function exec($sql){
        self::init();
        if($sql==""){
            return false;
        }
        try{
            return self::$pdo->exec($sql);
        }catch(Exception $e){
            return $e->getMessage();
        }
         
    }
    /**
    执行有返回值的查询，返回PDOStatement  可以通过链式操作，可以通过这个类封装的操作获取数据
    */
    public  static function query($sql){
        self::init();
        if($sql==""){
            return false;
        }
        self::$statement = self::$pdo->query($sql);
        return self::$statement;
    }
    /**
    开启事务
    */
    public  static function beginTA(){
        self::init();
        return self::$pdo->beginTransaction();
    }
    /**
    提交事务
    */
    public  static function commit(){
        self::init();
        return self::$pdo->commit();
    }
    /**
    事务回滚
    */
    public  static function rollBack(){
        self::init();
        return self::$pdo->rollBack();
    }
    public  static function lastInertId(){
        self::init();
        return $db->lastInsertId();
    }
     
     
     
     
    //**   PDOStatement 类操作封装    **//
     
    /**
    让模版执行SQL语句，1、执行编译好的 2、在执行时编译
    */
    public  static function execute($param=""){ 
        if(is_array($param)){
            try{
                return self::$statement->execute($param);
            }catch (Exception $e){
                //return self::$errorInfo();
                return $e->getMessage();
            }
        }else{
            try{
                return self::$statement->execute();
            }catch(Exception $e){
                /* 返回的错误信息格式
                [0] => 42S22
                [1] => 1054
                [2] => Unknown column 'col' in 'field list'
                return self::$errorInfo();
                */
                return $e->getMessage();
            }
        }
    }
     
    /**
    参数1说明：
    PDO::FETCH_BOTH     也是默认的，两者都有（索引，关联）
    PDO::FETCH_ASSOC    关联数组
    PDO::FETCH_NUM      索引
    PDO::FETCH_OBJ          对象
    PDO::FETCH_LAZY     对象 会附带queryString查询SQL语句
    PDO::FETCH_BOUND    如果设置了bindColumn，则使用该参数
    */
    public  static function fetch($fetch_style=PDO::FETCH_BOTH){
        if(is_object(self::$statement)){
            return self::$statement->fetch($fetch_style);
        }else{
            return false;
        }
    }
    /**
    参数1说明：
    PDO::FETCH_BOTH     也是默认的，两者都有（索引，关联）
    PDO::FETCH_ASSOC    关联数组
    PDO::FETCH_NUM      索引
    PDO::FETCH_OBJ          对象
    PDO::FETCH_COLUMN   指定列 参数2可以指定要获取的列
    PDO::FETCH_CLASS        指定自己定义的类
    PDO::FETCH_FUNC     自定义类 处理返回的数据
    PDO_FETCH_BOUND 如果你需要设置bindColumn，则使用该参数
    参数2说明：
    给定要处理这个结果的类或函数
    */
    public  static function fetchAll($fetch_style=\PDO::FETCH_BOTH,$handle=''){
        if($handle!=''){
            return self::$statement->fetchAll($fetch_style,$handle);
        }else{
            return self::$statement->fetchAll($fetch_style);
        }
    }
    /**
    以对象形式返回 结果 跟fetch(PDO::FETCH_OBJ)一样
    */
    public  static function fetchObject($class_name = ''){
        if($class_name!=''){
            return self::$statement->fetchObject($class_name);
        }else{
            return self::$statement->fetchObject();
        }
    }
     
    /**
    public  static function bindColumn($array=array(),$type=EXTR_OVERWRITE){
        if(count($array)>0){
            extract($array,$type);
        }
        //self::$statement->bindColumn()
    }
    */
     
    /**
    以引用的方式绑定变量到占位符(可以只执行一次prepare，执行多次bindParam达到重复使用的效果)
    */
    public  static function bindParam($parameter,$variable,$data_type=PDO::PARAM_STR,$length=6){
        return self::$statement->bindParam($parameter,$variable,$data_type,$length);
    }
     
    /**
    返回statement记录集的行数
    */
    public  static function rowCount(){
        return self::$statement->rowCount();
    }
    public  static function count(){
        return self::$statement->rowCount();
    }
     
     
    /**
    关闭编译的模版
    */
    public  static function close(){
        return self::$statement->closeCursor();
    }
    public  static function closeCursor(){
        return self::$statement->closeCursor();
    }
    /**
    返回错误信息也包括错误号
    */
    private  static function errorInfo(){
        return self::$statement->errorInfo();
    }
    /**
    返回错误号
    */
    private  static function errorCode(){
        return self::$statement->errorCode();
    }
     
     
     
    //简化操作
    public  static function insert($table,$data){
        if(!is_array($data)){
            return false;
        }
        $cols = array();
        $vals = array();
        foreach($data as $key=>$val){
            $cols[]="`{$key}`";
            $vals[]="'".self::addsla($val)."'";
        }
        $sql  = "INSERT INTO {$table} (";
        $sql .= implode(",",$cols).") VALUES (";        
        $sql .= implode(",",$vals).")";
        return self::exec($sql);
    }
    public  static function update($table,$data,$where=""){
        if(!is_array($data)){
            return false;
        }
        $set = array();
        foreach($data as $key=>$val){
            $set[] = "`{$key}`"."='".trim(self::addsla($val))."'";
        }
        if(is_array($where)){
            foreach ($where as $key => $value) {
                $tmp[] = "`{$key}`"."='".trim(self::addsla($value))."'";
            }
            $where = implode(' AND ', $tmp);
        }
        $sql = "UPDATE {$table} SET ";
        $sql .= implode(",",$set);
        $sql .= " WHERE ".$where;
        return self::exec($sql);
    }
    public  static function delete($table,$where=""){
        if(is_array($where)){
            foreach ($where as $key => $value) {
                $tmp[] = "`{$key}`"."='".trim(self::addsla($value))."'";
            }
            $where = implode(' AND ', $tmp);
        }
        $sql = "DELETE FROM {$table} WHERE ".$where;
        return self::exec($sql);
    }
    public  static function select($table,$where="",$page=1,$size=20,$field='*',$order=''){
        $star = ($page-1)*$size;
        if(is_array($where)){
            foreach ($where as $key => $value) {
                $tmp[] = $key."='".trim(self::addsla($value))."'";
            }
            $where = implode(' AND ', $tmp);
        }
        $where = $where ? ' WHERE '.$where : '';
        $order = $order ? ' ORDER BY '.$order : '';
        $sql = "SELECT {$field} FROM {$table} ".$where.$order." LIMIT {$star},{$size}";
        self::query($sql);
        $rows = self::fetchAll();
        foreach ($rows as $key => $value)
        {
            foreach ($value as $k => $val)
            {
                if (is_numeric($k)) unset($value[$k]);
            }
            $rows[$key] = $value;
        }
        return $rows;
    }
    public  static function selectSql($sql){
        self::query($sql);
        $rows = self::fetchAll();
        foreach ($rows as $key => $value)
        {
            foreach ($value as $k => $val)
            {
                if (is_numeric($k)) unset($value[$k]);
            }
            $rows[$key] = $value;
        }
        return $rows;
    }
    public  static function find($table,$where="",$field='*'){
        if(is_array($where)){
            foreach ($where as $key => $value) {
                $tmp[] = $key."='".trim(self::addsla($value))."'";
            }
            $where = implode(' AND ', $tmp);
        }
        $where = $where ? ' WHERE '.$where : '';
        $sql = "SELECT {$field} FROM {$table} ".$where;
        self::query($sql);
        return self::fetchObject();
    }
     
    private  static function addsla($data){
        if(self::$is_addsla){
            return trim(addslashes($data));
        }
        return $data;
    }
}