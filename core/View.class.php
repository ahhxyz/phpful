<?php
namespace phpful\core;

class View {
	//private $tpl_file;
	private $_tpl;//解析后的模板内容;
	private $_config=array();
	private $tag=array();
	private $tagBegin='<';
	private $tagEnd='>';
	private $ifCon=array();
	private $inc_tpl;
	public function __construct($tpl_file){
        if(!file_get_contents($tpl_file)){
            exit('模板内容读取失败!');
        }else{
			$this->_tpl=file_get_contents($tpl_file);  
		}
		$this->_config=array(
		        '__TPL__'=>__TPL__,
	            '__INSTALL_PATH__'=>INSTALL_PATH,
	            '__CFG__'=>'<script type="text/javascript">
			                var APP_TYPE="php";
							var PHP_RUN="php";
							var RUN=PHP_RUN+"/run.php";
			                </script>',
				'<!--{'=>'',
				'}-->'=>'',
		);
        $this->parseConfig();

		$this->ifCon=array(
	            'eq'=>'==',
		        'neq'=>'!=',
	            'lt'=>'<',
	            'gt'=>'>',
		        'or'=>'||',
		        'and'=>'&&',
	    );

	
	}
	



public function parse(){
       //$this->parseConfig();
       //$this->parseCommon();
	$this->parseForeach();
	$this->parseIf();
	$this->parseVar();
	$this->parseSwitch();
	$this->parseInclude();

	return $this->_tpl;

}
/*
解析系统常量;

*/

public function parseConfig(){
		    //在java中， var JAVA_RUN="java";var RUN=JAVA_RUN+"/run.java";
    foreach($this->_config as $key=>$val){
	  $this->_tpl=str_replace($key,$val,$this->_tpl);
	  }
	
}




public function parseForeach(){
    $modeForeach='/'.$this->tagBegin.'foreach\s+val=(\"|\')([\w]+)(\"|\')\s+from=(\"|\')([\w]+)(\"|\')(|\s)'.$this->tagEnd.'/';//一个圆括号里面的匹配内容对应一个匹配结果;
    $modeForeach2='/'.$this->tagBegin.'foreach\s+key=(\"|\')([\w]+)(\"|\')\s+val=(\"|\')([\w]+)(\"|\')\s+from=(\"|\')([\w]+)(\"|\')(|\s)'.$this->tagEnd.'/';
    $modeEndForeach='/'.$this->tagBegin.'\/foreach'.$this->tagEnd.'/';
    if(preg_match($modeForeach,$this->_tpl)){
	$this->_tpl=preg_replace($modeForeach,"<?php foreach(\$this->_vars['$5'] as \$$2 ){?>",$this->_tpl);
    }
	
	if(preg_match($modeForeach2,$this->_tpl)){
	$this->_tpl=preg_replace($modeForeach2,"<?php foreach(\$this->_vars['$8'] as \$$2=>\$$5){?>",$this->_tpl);
	}

    $this->_tpl=preg_replace($modeEndForeach,"<?php }?>",$this->_tpl);
    $this->parseVar();
    return true;
		
	}



public function parseVar(){
	$mode='/\{\$([\w]+)\}/';
	$mode2='/\{\$([\w]+)\[(\"|\')([\w]+)(\"|\')\]\}/';
	$mode3='/\{\$([\w]+)\.([\w]+)\}/';
	
   if(preg_match($mode,$this->_tpl)){
    $this->_tpl=preg_replace($mode,"<?php echo \$this->_vars['$1'];?>",$this->_tpl);//替换成index.php文件中注入的变量
    }
   if(preg_match($mode2,$this->_tpl)){
    $this->_tpl=preg_replace($mode2,"<?php echo \$$1['$3'];?>",$this->_tpl);//替换成index.php文件中注入的变量
    }
   if(preg_match($mode3,$this->_tpl)){
    $this->_tpl=preg_replace($mode2,"<?php echo \$$1['$2'];?>",$this->_tpl);//替换成index.php文件中注入的变量
    }
		
}



public function parseIf(){
	$mode='([\w\s\$]+)';
    $modeIf='/'.$this->tagBegin.'if\s+condition=(\"|\')([^'.$this->tagEnd.']*?)[\/]*(\"|\')(|\s)'.$this->tagEnd.'/';
	//下面这条语句表示对引号之间的内容匹配任意字符，且只匹配到后面的结束标记就停止匹配而不再向后匹配;
    $modeElseIf='/'.$this->tagBegin.'elseif\s+condition=(\'|\")([^'.$this->tagEnd.']*?)(\'|\")(|\s)\/'.$this->tagEnd.'/';
    $modeIf2='/'.$this->tagBegin.'if\s+condition=(\'|\")\('.$mode.'\)\s([\w]+)+\s\('.$mode.'\)(\'|\")(|\s)'.$this->tagEnd.'/';
    $modeElseIf2='/'.$this->tagBegin.'elseif\s+condition=(\'|\")\('.$mode.'\)\s([\w]+)+\s\('.$mode.'\)(\'|\")(|\s)\/'.$this->tagEnd.'/';
	$modeElse='/'.$this->tagBegin.'else(|\s)\/'.$this->tagEnd.'/';

	$modeEndIf='/'.$this->tagBegin.'\/if'.$this->tagEnd.'/';

    if(	preg_match($modeIf,$this->_tpl,$r_modeIf)){
		$this->_tpl=preg_replace($modeIf,'<?php if( '.$this->parstCondition($r_modeIf['2']).' )  {?>',$this->_tpl);
    }

	if(	preg_match($modeIf2,$this->_tpl,$r_modeIf2)){
		$c='('.$r_modeIf2['2'].') '.$this->ifCon[strtolower($r_modeIf2['3'])].'( '.$r_modeIf2['4'].')';
		$this->_tpl=preg_replace($modeIf2,'<?php if( '.$this->parstCondition($c).' ) {?>',$this->_tpl);
	}    

   if(	preg_match($modeElseIf,$this->_tpl,$r_modeElseIf)){
		 $this->_tpl=preg_replace($modeElseIf,'<?php } elseif( '.$this->parstCondition($r_modeElseIf['2']).' ) { ?>',$this->_tpl);
    } 

    if(	preg_match($modeElseIf2,$this->_tpl,$r)){
		 $c='('.$r['2'].') '.$con[strtolower($r['3'])].'( '.$r['4'].')';
		$this->_tpl=preg_replace($modeElseIf2,'<?php } elseif( '.$this->parstCondition($c).' ) {?>',$this->_tpl);
    }    

    if(	preg_match($modeElse,$this->_tpl,$r)){

		$this->_tpl=preg_replace($modeElse,'<?php } else {?>',$this->_tpl);
    }  

	$this->_tpl=preg_replace($modeEndIf,'<?php } ?>',$this->_tpl);


}



public function parseInclude(){ 
	$modeInc='/'.$this->tagBegin.'include\s+file=(\"|\')([\w\.]+)(\"|\')(|\s)'.$this->tagEnd.'/';
	if(preg_match($modeInc,$this->_tpl,$r_modeInc)){
		$include=file_get_contents($r_modeInc['2']);			
		$inc=new view($r_modeInc['2']);
		$inc_str=$inc->parse();
		$this->_tpl=preg_replace($modeInc,'<?php { ?>  '.$inc_str.'<?php }?>',$this->_tpl);
	}	
		
}



protected function parseSwitch(){

	$modeSwitch='/'.$this->tagBegin.'switch\s+name=(\"|\')([\w]+)+(\"|\')(|\s)'.$this->tagEnd.'/';
	$modeCase='/'.$this->tagBegin.'case\s+val=(\"|\')([\w]+)+(\"|\')(|\s)'.$this->tagEnd.'/';
	$modeEndCase='/'.$this->tagBegin.'\/case'.$this->tagEnd.'/';

	$modeDefault='/'.$this->tagBegin.'default\/'.$this->tagEnd.'/';
	$modeEndSwitch='/'.$this->tagBegin.'\/switch'.$this->tagEnd.'/';
	$a=preg_match($modeSwitch,$this->_tpl,$r_modeSwitch);
   // var_dump($a);
	if(preg_match($modeSwitch,$this->_tpl,$r_modeSwitch)){
		$this->_tpl=preg_replace($modeSwitch,'<?php switch( \$this->_vars[\''.$r_modeSwitch["2"].'\']){?>',$this->_tpl);
	}


    if(preg_match($modeCase,$this->_tpl,$r_modeCase)){
		$this->_tpl=preg_replace($modeCase,'<?php  case  \''.$r_modeCase["2"].'\' :?>',$this->_tpl);
	}

    if(preg_match($modeEndCase,$this->_tpl,$r_modeEndCase)){
		$this->_tpl=preg_replace($modeEndCase,'<?php break;?>',$this->_tpl);
	}

    if(preg_match($modeDefault,$this->_tpl,$r_modeDefault)){
		$this->_tpl=preg_replace($modeDefault,'<?php default: ?>',$this->_tpl);
	}

    if(preg_match($modeEndSwitch,$this->_tpl,$r_modeEndSwitch)){
		$this->_tpl=preg_replace($modeEndSwitch,'<?php }?>',$this->_tpl);
	}
		
}
private function parstCondition($c){
	foreach($this->ifCon as $key=>$val){
		if(stripos($c,$key)){
			$c=str_replace($key,$val,strtolower($c));
		}
	}
    return $c;
	
}
	
}
?>
