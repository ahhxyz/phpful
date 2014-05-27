<?php
/*
 *此页面只接受客户端的一次请求就会建立全双工的连接，不用反复请求。
 */
namespace pbay; 
$ws = new WebSocket("localhost",10001);	//
?>