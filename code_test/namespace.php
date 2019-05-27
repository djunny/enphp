<?php
namespace z {

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

    class_alias('\z\a', '\z\b');

    //echo "{$_SERVER[HTTP_HOST]}{$_SERVER[REQUEST_URI]}";
    //echo $_SERVER['REQUEST_TIME'];
    $s = new \z\a();
    $s->b('g');
    echo $s->g;
}


?>