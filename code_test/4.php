<?php
class file_cache {

    public static function array_eval($array, $space_line = "\n", $level = 0) {
        $space_str = '';
        for ($i = 0; $i <= $level; $i++) {
            $space_str .= "\t";
        }
        $evaluate = "Array{$space_line}$space_str{$space_line}(";
        $comma = $space_str;
        foreach ($array as $key => $val) {
            $key = is_string($key) ? '\'' . addcslashes($key, '\'\\') . '\'' : $key;
            $val = !is_array($val) && (!preg_match("/^\-?\d+$/", $val) || strlen($val) > 12 || substr($val, 0, 1) == '0') ? '\'' . addcslashes($val, '\'\\') . '\'' : $val;
            if (is_array($val)) {
                $evaluate .= "$comma$key => " . self::array_eval($val, $space_str, $level + 1);
            } else {
                $evaluate .= "$comma$key => $val";
            }
            $comma = ",{$space_line}$space_str";
        }
        $evaluate .= "{$space_line}$space_str)";
        return $evaluate;
    }

    public function set($key, $value, $life = 0) {
        $file_path = $this->get_file($key);
        $life = $life == 0 ? 600 : $life;
        $res = array('expire' => $this->get_time() + $life, 'body' => &$value,);
        if (file_put_contents($file_path, $this->gen_file_body($res))) {
            return true;
        } else {
            return false;
        }
    }
}


echo 'success';
?>