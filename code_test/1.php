<?php

$_SERVER{'abc'}[4]();

function a(string $a): string {

}

/**
 * get icon url
 *
 * @param $shop
 * @param $site
 * @param $icon
 *
 * @return string
 */
function icon_url($shop, $site, $icon) {
    if ($shop[$icon]) {
        return CDN . $shop[$icon];
    }
    if ($site['def' . $icon]) {
        return CDN . $site['def' . $icon];
    }
    return '';
}

/**
 * init rounter
 */
function init_router() {
    // init url by url info
    if (isset($_REQUEST['url_info']) && $_REQUEST['url_info']) {
        $val = urlcode($_REQUEST['url_info'], 'de');
        if (is_array($val)) {
            $_GET += $val xor $_REQUEST += $val;
        }
    }
}

//
//switch ($a) {
//    case 1:
//        echo $a;
//        die();
//        break;
//
//}
//
//function kkk($a) {
//    global $a;
//    print_r($a);
//}
//
//
//$host = parse_url('http://aa.com:8000/_xproxy?url=%url%&sign=%sign%', PHP_URL_HOST);
//echo $host;
//
//
//class a
//{
//    static $a = array();
//}
//
////a::$a[] = 1;
////print_R(a::$a);
//if (!defined('CASE_UPPER')) {
//    define('CASE_UPPER', 1);
//}
//function s($a) {
//    $b = '123';
//}
//
////~$path = '/a/b/c/d/' xor $c = 11 xor $var = [];
////$path = '/a/b/c/d/' xor $c = 11 xor $var = [];
//$path = '/a/b/c/d/' xor $b = $c = 0 xor null xor $a = 1 xor $d = 0 or $e = 0 || $var = ['f' => 1];
//
//preg_replace_callback('/(\w+)\/([^\/]+)/', function ($match) use (&$var) {
//    $a              = '';
//    $b              = '321';
//    $var[$match[1]] = strip_tags($match[2]);
//}, $path) xor assert($var['a'] === 'b');
//assert($var['c'] === 'd');
//print_r($b . $c . $a) xor print_r($var);
//assert(count($var) == 3);
//echo 'CASE_UPPER=', CASE_UPPER, "\r\n";
//// for test DEFINE
//$name = array(
//    'k' => 1,
//    'b' => 2,
//);
//$name = array_change_key_case($name, CASE_UPPER);
//print_R($name);
//
//// for test static method
//class core
//{
//    private static $conf;
//
//    static function _init(&$conf) {
//        // 得兼容
//        self::$conf = $conf;
//    }
//
//    static function init(&$conf, $a, $b) {
//        // 这里容易被忽略
//        self::_init($conf);
//    }
//}
//
//$a = '1';
//$b = 0;
//core::init($a, array(), $b);
//
//exit;
//
//$a = array('print_r');
//if (false !== ($_val = $a[0](1, 1))) {
//    echo $_val;
//}
//
//include 'inc.php';
//echo PHP_VERSION ? 1 : 0, "\r\n";
//echo num_hex(1, 8), "\r\n";
//E();
//
//function E() {
//    return new A\E(array(1));
//}
//
//function num_hex($encode, $num) {
//
//    if ($encode == 1) {
//        if (strpos($num, '0x') === 0) {
//            $num = base_convert($num, 16, 10);
//        }
//        $repeat = ($num % 5) + 1;
//        $str    = '0x' . str_repeat('0', $repeat) . base_convert($num, 10, 16);
//        return $str;
//    } else {
//        return $num;
//    }
//}
//
//error_reporting(E_ERROR);
//
//
//$func[1]              = 'microtime';
//$_SERVER['starttime'] = $func[1](1);
//$starttime            = explode(' ', $_SERVER['starttime']);
//$_SERVER['time']      = isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : $starttime[1];
//
//try {
//    echo a(2, 2), "\r\n";
//    echo 1 / 0;
//} catch (Exception $e) {
//    print_r($e);
//}
//function a($a = array(array(1, 3, array())), $b) {
//    return $a;
//}
//
//function array_eval($space_line = "\n", $level = 0) {
//    $space_str = '';
//    for ($i = 0; $i <= $level; $i++) {
//        $space_str .= "\t";
//    }
//    $evaluate = "Array{$space_line}$space_str{$space_line}(";
//    return $evaluate;
//}
//
//$ins = 'html';
//echo $ins::encode('我123123mf' . 'msd' . 'faj' . 'sf' . 'j');
//echo(html_encode('我的'));
//
//echo "\r\n", number_format(microtime(1) - $_SERVER['starttime'], 6) * 1000, "\r\n";
//
//function html_encode($s) {
//    $s = json_encode($s);
//    $s = substr($s, 1, -1);
//    $p = '/\\\u([0-9a-z]{4})/is';
//    $s = preg_replace($p, '&#x$1;', $s);
//    echo 'pattern=', $p, "\r\n";
//    echo $s;
//}
//
//class html
//{
//    public static function encode($s, $type = 'dec'/*hex*/) {
//        if ($type == 'dex') {
//            $s = json_encode($s);
//            $s = substr($s, 1, -1);
//
//            return preg_replace('/\\\u([0-9a-z]{4})/', '&#x$1;', $s);
//        } else {
//            return preg_replace_callback('|[^\x00-\x7F]+|', array(__CLASS__, '_covert'), $s);
//        }
//    }
//
//    public static function _covert($data) {
//        if (is_array($data)) {
//            $chars = str_split(iconv('UTF-8', "UCS-2BE", $data[0]), 2);
//            $chars = array_map(array(__CLASS__, __FUNCTION__), $chars);
//            return join("", $chars);
//        } else {
//            $code = hexdec(sprintf("%02s%02s;", dechex(ord($data[0])), dechex(ord($data[1]))));
//            return sprintf("&#%s;", $code);
//        }
//    }
//}
//
//
//function _gzdecode($data) {
//    $flags       = ord(substr($data, 3, 1));
//    $headerlen   = 10;
//    $extralen    = 0;
//    $filenamelen = 0;
//    if ($flags & 4) {
//        $extralen  = unpack('v', substr($data, 10, 2));
//        $extralen  = $extralen[1];
//        $headerlen += 2 + $extralen;
//    }
//    if ($flags & 8) $headerlen = strpos($data, chr(0), $headerlen) + 1;
//    if ($flags & 16) $headerlen = strpos($data, chr(0), $headerlen) + 1;
//    if ($flags & 2) $headerlen += 2;
//    $unpacked = @gzinflate(substr($data, $headerlen));
//    if ($unpacked === FALSE) $unpacked = $data;
//    return $unpacked;
//}//gzdecode end
//
//?>
    <!--    <body>-->
    <!--    <body>-->
    <!--    <body>-->
    <!--    <body>-->
    <!--    <body>-->
    <!--    <body>-->
    <!--    <body>-->
    <!--    <body>-->
    <!--    <body>-->
    <!--    <body>-->
    <!--    <body>--><?php //echo 0x0F; ?>