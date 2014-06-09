<?php
namespace phpful\Common;
function C (){
	$c= \phpful\core\Config::getInstance();
	return  $c->getAll();
}
?>