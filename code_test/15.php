<?php
error_reporting(E_ERROR);
class aa {
    static $MINIMUM_DIMENSION = 1;

    function getBlackMatrix() {
        $width  = 1;
        $height = 2;
        if ($width >= self::$MINIMUM_DIMENSION && $height >= self::$MINIMUM_DIMENSION) {

        }
    }
}

class a {
    public $g;

    function b($g) {
        $this->$g = $g;
    }
}

final class MathUtils {
    private function __construct() {
    }

    public static function round($d) {
        return (int)($d + ($d < 0.0 ? -0.5 : 0.5));
    }

    public static function distance($aX, $aY, $bX, $bY) {
        $xDiff = $aX - $bX;
        $yDiff = $aY - $bY;
        return (float)sqrt($xDiff * $xDiff + $yDiff * $yDiff);
    }
}

class Reader {

}

interface sReader {
}

abstract class mzReader extends Reader {
}
$_SERVER['HTTP_HOST'] = 1;
$_SERVER['REQUEST_URI'] = 1;

function main() {
    echo "{$_SERVER[HTTP_HOST]}{$_SERVER[REQUEST_URI]}";
    //echo $_SERVER['REQUEST_TIME'];
}
define('ROOT_PATH', __DIR__);
!defined('ROOT_PATH') && exit('123');

$s = new a();
$s->b('g');
echo $s->g;
echo main();
?>