<?php

class  d
{
    static $array = array();

    static function test() {
        echo "test", PHP_EOL;
        global $var;
        $path           = '/a/b/c/d/';
        $sta            = 'array';
        self::${$sta}[] = 1;
        if (1 == 2) {
        } else {
            //
            //$var = [];
            preg_replace_callback('/(\w+)\/([^\/]+)/', function ($match) use (&$var) {
                $var[$match[1]] = strip_tags($match[2]);
            }, $path);
            assert($var['a'] === 'b');
            assert($var['c'] === 'd');
            assert(count($var) == 2);
        }
        $var = array();
    }
}

function a() {
    $a = '123123';
    return $a;
}

d::test();
print_r($var);
echo a();

?>