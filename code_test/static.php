<?php
namespace {
    static $global = 1;

    interface i
    {
        function init($a, $b);
    }

    class ii implements i
    {

        function init($b, $c) {
            echo $b, $c;
        }
    }

    class a
    {
        public static $var;
        private       $pri;


        public function load_static() {
            global $global;
            static $conf;
            echo $global;
            $str = 'pri';
            if ($conf) {
            }
            if (self::$var) {

            }
            $this->pri = 1;
            if ($this->pri) {

            }
            if ($global) {

            }
        }


    }


    interface QrReader
    {
        public function decode($image);

        public function reset();
    }


    abstract class Binarizer
    {

        public abstract function getBlackRow($y, $row);

        public abstract function getBlackMatrix();

        public abstract function createBinarizer($source);

    }

    final class GenericGF
    {
        public static $AZTEC_DATA_12;
        public static $AZTEC_DATA_10;
        public static $AZTEC_DATA_6;
        public static $AZTEC_PARAM=6;
        public static $QR_CODE_FIELD_256;
        public static $DATA_MATRIX_FIELD_256;
        public static $AZTEC_DATA_8;
        public static $MAXICODE_FIELD_64;
        private       $expTable;
        private       $logTable;
        private       $zero;
        private       $one;
        private       $size;
        private       $primitive;
        private       $generatorBase;


        public static function Init() {
            self::$AZTEC_DATA_12         = new a(0x1069, 4096, 1);
            self::$AZTEC_DATA_10         = new a(0x409, 1024, 1);
            self::$AZTEC_DATA_6          = new a(0x43, 64, 1);
            self::$AZTEC_PARAM           = new a(0x13, 16, 1);
            self::$QR_CODE_FIELD_256     = new a(0x011D, 256, 0);
            self::$DATA_MATRIX_FIELD_256 = new a(0x012D, 256, 1);
            self::$AZTEC_DATA_8          = self::$DATA_MATRIX_FIELD_256;
            self::$MAXICODE_FIELD_64     = self::$AZTEC_DATA_6;
        }

    }

    if (PHP_EOL) {
    }
    $array = array(
        'a' => $b,
    );
    $func = 'load_static';
    $b     = (new a());
    $b->{$func}();
    $i = new ii();
    $i->init('a', 'b');
    $_SERVER['s'][0] = 'AZTEC_PARAM';
    echo GenericGF::${$_SERVER['s'][0]};
}

?>