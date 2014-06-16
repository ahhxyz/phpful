<?php
namespace phpful\core;
use phpful\core;

/*
 *只针对关系型数据库，且只使用PDO
 *构造函数的参数$args是个数组，表示动态的数据库配置信息
 */
class Db{
    private static   $instance;
    private $_pdo;//数据库连接
    private function __construct($args=""){ 
        $config=phpful\core\Config::getInstance()->getConfig(MODULE);
        $Db=$config["DB"];         
        $this->_pdo= new \PDO($Db["TYPE"].':host='.$Db["HOST"].';dbname='.$Db["DBNAME"].','.$Db["USER"].",".$Db["PWD"],array(\PDO::ATTR_PERSISTENT => true));
        
		

    }


    public static function getInstance($config,$num){
        if(!isset(self::$instance)){

            self::$instance=new Db();

        }
        return self::$instance;
    }
    
    /**
     * 获取某个数据表里面的所有字段
     * @param string $table：完整的数据表名
     * @return $columns or null
     */
    public function getColumns($table){
        $statement=  $this->_pdo->prepare('SELECT * from '.$table." LIMIT 1");
        $statement->execute();
        $res=$statement->fetch(\PDO::FETCH_ASSOC);
        if(!empty($res)){
            foreach($res as $key=>$val){
                 $columns[]=$key;			
            }
            
            return $columns;
        }
        return NULL;
    }
    public function select(){
		
	
        $statement=  $this->_pdo->prepare($sql);
        $statement->execute();		
        $res=$statement->fetchAll();
        //self::$instance=null;
        return $res;	
				
		/*
	SELECT 
	article.aid,article.title,
	user.username,
	type.typename 
	FROM article 
	JOIN user ON article.uid=user.uid 
	JOIN type ON article.tid=type.tid


		*/	

    }

    /**
     * 
     * @param type $table
     * @param type $fiels
     * @param type $values
     * @return 最后插入的数据行的ID
     */

    public function insert($table,$fiels,$values){
            

           
        $this->_pdo->exec('INSERT INTO '.$table.' ('.$fields.')'.' VALUES('.$values.') ');

        return   $this->_pdo->lastInsertId();
        

    }




    public function Put( $data=''){


        if(!isset($this->where)){
           echo json_encode(array('errorMsg'=>'必须设置where条件！'));
           return ;
        }

    foreach($data as $key=>$val){
               $sets[]='  '.$key.'="'.$val.'"';		   
            }

            $set=implode(',',$sets);

            $res= self::$instance->exec('UPDATE  '.$this->_table.' SET '.$set.' '.$this->where);
            //self::$instance=null;
            return $res;

    }







    public function Delete(){
        $res=self::$instance->exec('DELETE FROM '.$this->_table.$this->where);
            //self::$instance=null;
            return $res;
    }



public function getBy($field,$val){
	$select=isset($this->fields)?$this->fields:' * ';
	$res=self::$instance->query('SELECT '.$select.' FROM '.$this->_table.' WHERE '.$field.'="'.$val.'"');
	//self::$instance=null;
	return $res;
}



public function getFieldBy($field,array $args){		
	$statement=self::$instance->prepare('SELECT '.$args['1'].' FROM '.$this->_table.' WHERE '.$field.'="'.$args['0'].'"');
	$statement->execute();
	$res=$statement->fetchColumn();
	//self::$instance=null;
	return $res;
}



	/*
	过滤函数
	$val;要过滤的参数值;
	
	
	
	*/
	public function filter($data){
		if(is_array($data)){

		}else{

		}
		return $data;
		
	}



	/*
	取得当前用户数据表的总记录数;
	*/
	public function Count(){

	}


	public function Max($field){
	    
	}

	public function Min($field){

	}
	public function Avg($field){

	}

	public function Sum($field){

	}

		/*
		如果接收的数据中有字段不需插入数据库，那么就在字段验证后剔除这些字段；
		
		*/
	public function no_repeat(){
	 	foreach($this->no_repeat as $val){
			unset($this->data[$val]);			
		}
	
		return $this->data;
	}






	public function addPrefix($val){
		preg_match_all('/(on(|\s)).*?(\.)/is',$val,$on); 
	    $sOn=$on['0']['0'];
		$rOn=trim(str_replace('.','',str_replace('on','',$on['0']['0'])))!='admin'?str_replace('on','on '.$this->prefix,str_replace(' ','',$sOn)):$sOn;
		$val=str_ireplace($sOn,$rOn,$val);
		preg_match_all('/(=(|\s)).*?(\.)/is',$val,$eq); 
	    $sEq=$eq['0']['0'];
		$rEq=trim(str_replace('.','',str_replace('=','',$eq['0']['0'])))!='admin'?str_replace('=','='.$this->prefix,str_replace(' ','',$sEq)):$sEq;
		$val=str_ireplace($sEq,$rEq,$val);
		return $val;
						
	}

}


	
	

?>