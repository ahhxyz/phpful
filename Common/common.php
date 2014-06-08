<?php
namespace phpful\Common;
function C (){
	$c= \phpful\core\Config::getIns();
	return  $c->getAll();
}
?>