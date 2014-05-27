<?php
/*
 *权限控制类
 */
class Auth{

function __call($className, $arguments){        
    $class = new ReflectionClass($className);
    return $class->newInstanceArgs($arguments);
}


/*
 @生成对象的工厂方法，比如：控制器对象、模型对象、视图对象等
------------------------------------------------------------------------------------------------------------------------------------------------------------    
*/
}
?>