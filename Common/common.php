<?php
namespace phpful\Common;
function C (){
	$c= \phpful\Core\Config::getIns();
	return  $c->getAll();
}
?>