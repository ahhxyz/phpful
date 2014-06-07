<?php
namespace phpful\Core;
use phpful\Lib\Db;

class pdo extends Db {
    private $pdo;
    /*
     @每个应用程序只能有一个数据库的实例，所以这个类使用了单例模式
    */
    private function  __construct($db=''){


    }
    public function Get(){
        $statement=$this->conn[$this->conn_num]->prepare($sql);
        $statement->execute();
        if (!empty($id)) {
            return  $statement->fetch(PDO::FETCH_NAMED);//该函数返回从select语句执行的结果集中取出的一条数据，失败返回false;

        }else{
            return  $statement->fetchAll(PDO::FETCH_NAMED);//该函数返回从select语句执行的结果集中取出的数据,失败返回false; 
        }
       $statement->closeCursor();//关闭连接，虽然这样可能会影响性能，但不会一直占用数据库连接。
    }
    


    public function Post(){

    }

    public function Put(){

    }

    public function Delete(){
        
    }


    /*
     *这个函数很不合理，增删改查应该分别对应一个方法以降低耦合性。后续使用那4个方法来替代这个方法；
     */


}
?>