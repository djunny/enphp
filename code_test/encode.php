<?php

/**
 * User: djunny
 * Date: 2016-11-22
 * Time: 16:32
 * Mail: 199962760@qq.com
 */
class core
{
    static $aaa = 1;

    static function addslashes(&$var) {
        if (is_array($var)) {
            foreach ($var as $k => &$v) {
                core::addslashes($v);
            }
        } else {
            $var = addslashes($var);
        }
        return $var;
    }
}

$array = array('1');
core::addslashes($array);
core::$aaa = 2;
echo core::$aaa;

$a = /*<encode>*/
    'format_code'/*</encode>*/
;
$b = /*<encode>*/str_rot13('1')/*</encode>*/
;
$c = /*<encode>*/
    1/*</encode>*/
;
$d = /*<encode>*/
    3/*</encode>*/
;
assert($c == 1);
assert($d == 3);
assert($a == 'format_code');
echo $a;


?>