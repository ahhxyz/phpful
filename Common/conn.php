<?php
//独立版的一下内容由安装页面获取的数据来写入;
return array(
  'erpadmin'=>array(
	  'dsn'=>'mysql',//主机类型，数据库类型变更时须更改此配置,连接postgesql数据库时，值为：'pgsql';
      'host'=>'localhost',//主机名称
	  'port'=>'3306',
      'user'=>'root',
      'pwd'=>'',
	  'default'=>'true',
	 // 'prefix'=>'xr_',//公用版不进行此项配置;

	),
	//一般用户只有一个数据库,数据库名不能相同，否则会报错，因为数组的索引不能相同,
	'erpa'=>array(
	  'dsn'=>'pgsql',//主机类型，数据库类型变更时须更改此配置；
      'host'=>'localhost',//主机名称
	  'port'=>'5432',
      'user'=>'postgres',
      'pwd'=>'Scj0912',
	  'default'=>'false',
	),
	
	
	
	
	
	);
          



?>