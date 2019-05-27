<?php

class child_class
{
    const a = '1';
    const b = '2';

    function call($a, $b) {
        echo $a, PHP_EOL, $b, PHP_EOL;
    }
}

class parent_class
{
    public $child;

    public function __construct(&$conf) {
        $this->child = new child_class();
        $this->child->call(child_class::a, child_class::b);
    }
}


$conf = array();
$a    = new parent_class($conf);
?>