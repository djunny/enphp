<?php
/**
 * User: djunny
 * Date: 2016-01-26
 * Time: 20:56
 * Mail: 199962760@qq.com
 */
print_R('a');
function parse_name($name, $type = 0) {
    if ($type) {
        return ucfirst(preg_replace_callback('/_([a-zA-Z])/', function ($match) {
            return strtoupper($match[1]);
        }, $name));
    } else {
        return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
    }
}
?>