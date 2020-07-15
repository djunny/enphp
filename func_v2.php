<?php
$_SERVER['starttime'] = microtime(1);
$starttime            = explode(' ', $_SERVER['starttime']);
$_SERVER['time']      = $starttime[1];

/**
 * enphp content
 *
 * @param       $file
 * @param array $options
 *
 * @return string
 */
function enphp($content, $options = array()) {
    $deep            = max(1, isset($options['deep']) ? (int)$options['deep'] : 1);
    $deep            = min($deep, 10);
    $options['deep'] = max(1, min($deep, 1));
    foreach (range(1, $deep) as $loop) {
        $content = strip_whitespace($content, $options);
    }
    return $content;
}

/**
 * enphp file
 *
 * @param $file
 * @param $target_file
 * @param $options
 *
 * @return string
 */
function enphp_file($file, $target_file, $options = array()) {
    $content = file_get_contents($file);
    check_bom($content);
    $content = enphp($content, $options);
    if ($target_file) {
        file_put_contents($target_file, $content);
    }
    return $content;
}

/**
 * strip white space
 *
 * @param       $content
 * @param array $options
 *
 * @return string
 */
function strip_whitespace($content, $options = array()) {

    format_code($content);
    $list                 = token_get_all($content);
    $last_space           = false;
    $is_function          = false;
    $function_var_list    = array();
    $static_fun_list      = array();
    $use_var_list         = array();
    $is_global            = true;
    $function_stack       = array();
    $str_var_list         = array();
    $is_class             = false;
    $class_name           = array();
    $class_stack          = 0;
    $function_var_close   = 1;
    $function_alias       = array();
    $is_string_var        = false;
    $is_static_var        = false;
    $is_interface         = false;
    $is_namespace         = false;
    $namespace_name       = '';
    $is_catch             = false;
    $is_throw             = false;
    $is_ns_separator      = false;
    $is_static_call       = false;
    $is_if                = false;
    $is_short_if          = false;
    $is_for               = false;
    $is_elseif            = false;
    $is_function_use      = false;
    $is_quote             = false;
    $is_abstract_function = false;
    $is_abstract_class    = false;
    $heredoc_end          = false;
    $global_vars          = array();
    $function_start_point = array();
    $options              = array_merge(array(
        //混淆方法名 1=字母混淆 2=乱码混淆 0=不混淆
        'ob_function'        => 2,
        //混淆函数产生变量最大长度
        'ob_function_length' => 3,
        //混淆函数调用 1=混淆 0=不混淆 或者 array('eval', 'strpos') 为混淆指定方法
        'ob_call'            => 1,
        //随机插入乱码
        'insert_mess'        => 1,
        //混淆方法调用 1=字母混淆 2=乱码混淆 0=不混淆
        'ob_class'           => 2,
        //混淆函数调用变量产生模式  1=字母混淆 2=乱码混淆 0=不混淆
        'encode_call'        => 2,
        //混淆变量 方法参数  1=字母混淆 2=乱码混淆 0=不混淆
        'encode_var'         => 2,
        //混淆变量最大长度
        'encode_var_length'  => 5,
        //混淆字符串常量  1=字母混淆 2=乱码混淆 0=不混淆
        'encode_str'         => 2,
        //混淆字符串常量变量最大长度
        'encode_str_length'  => 3,
        // 混淆html 1=混淆 0=不混淆
        'encode_html'        => 1,
        // 混淆数字 1=混淆为0x00a 0=不混淆
        'encode_number'      => 1,
        // 混淆的字符串 以 gzencode 形式压缩 1=压缩 0=不压缩
        'encode_gz'          => 1,
        // 加换行（增加可阅读性）
        'new_line'           => 0,
        // 移除注释 1=移除 0=保留
        'remove_comment'     => 1,
        // 文件头部增加的注释
        'comment'            => '-- enphp : https://github.com/djunny/enphp',
        // debug
        'debug'              => 1,
        // 重复加密次数，加密次数越多反编译可能性越小，但性能会成倍降低
        'deep'               => 1,
        // 默认PHP版本
        'php'                => PHP_MAJOR_VERSION,
    ), $options);
    $is_debug             = $options['debug'];
    $php_version          = $options['php'];

    $str_var_name    = substr(generate_name($options['encode_str'], $options['encode_str_length']), 1);
    $str_define_name = substr(generate_name($options['encode_str'], $options['encode_str_length']), 1);
    // 这里必须要多个字符防止多重混淆时，产生碰撞导致分隔符失效
    static $str_var_splits = array();
    $str_var_char = '';
    while (true) {
        $str_var_char = '';
        foreach (range(1, max($options['deep'], 3)) as $deep) {
            $str_var_char .= '|' . ($options['encode_gz'] ? chr(rand(1, 3 + min($deep, 6))) : strip_str(chr(rand(32, 64))));
            //base_convert(PHP_INT_MAX, 10, 16);// . chr(rand(1, 10)) . chr(rand(1, 10));
        }
        if (isset($str_var_splits[$str_var_char])) {
            continue;
        }
        $str_var_splits[$str_var_char] = 1;
        break;
    }
    $str_index   = 0;
    $str_var_str = array();
    $global_vars = array('$' . 'GLOBALS', '$_' . 'GET', '$' . '_SERVER');

    shuffle($global_vars);
    $str_global     = end($global_vars);
    $str_global_var = $str_global . '{' . $str_define_name . '}';

    $same_quotes = array('{' => '[', '}' => ']');
    /*
    switch (rand(0, 1)) {
        case 0:
            $str_global_var = $str_global . '{' . $str_define_name . '}';
            break;
        case 1:
            $str_global_var = $str_global . '[' . $str_define_name . ']';
            break;
    }*/
    $len_global_var = strlen($str_global_var);
    //foreach ($list as $key => &$val) {
    $all_start_time         = time();
    $insert_list            = array();
    $is_ob_array            = is_array($options['ob_call']);
    $allow_modify_variables = array('T_VARIABLE', 'T_INLINE_HTML', 'T_STRING', 'T_CONSTANT_ENCAPSED_STRING');
    for ($key = 0; $key < count($list); $key++) {
        $start_time = microtime_float();
        //list($time, $start_time) = explode('.', );
        $val = &$list[$key];
        //log::info($val);
        $trim_last = false;
        if (is_array($val)) {
            $token_idx  = $val[0];
            $token_name = is_numeric($token_idx) ? token_name($token_idx) : '';
            $token_str  = $val[1];
            //echo $token_str, "\r\n";
            switch ($token_idx) {
                //过滤空格
                case T_WHITESPACE:
                    $is_static_call && $is_static_call = 0;
                    if (!$last_space) {
                        $last_space = true;
                        $val[1]     = ' ';
                    } else if (!$options['new_line']) {
                        $val[1] = '';
                    }
                    break;
                case T_NAMESPACE:
                    $is_namespace   = 1;
                    $namespace_name = '';
                    $val[1]         = ' ' . trim($val[1]) . ' ';
                    $last_space     = true;
                    break;
                case T_INTERFACE:
                    $is_interface = 1;
                    $last_space   = false;
                    $is_class     = 1;
                    $class_name   = array();
                    break;
                case T_ABSTRACT:
                    if (find_next_token($list, $key + 1, array('function'))) {
                        $is_abstract_function = 1;
                    } else if (find_next_token($list, $key + 1, array('class'))) {
                        $is_abstract_class = 1;
                    }
                    $last_space = false;
                    break;
                case T_NS_SEPARATOR:
                    !$is_ns_separator && $is_ns_separator = 1;
                    break;
                case T_VARIABLE:
                    if (in_array($token_str, array('$_SERVER', '$_GET', '$_POST',
                                                   '$_COOKIE', '$_REQUEST', '$this',
                                                   '$GLOBALS', '$_SESSION', '$_FILES',
                                                   '$_ENV'))
                    ) {
                    } else {
                        // is ->
                        /*if (find_last_token($list, $key, array('->', '::'))) {
                            break;
                            // add list
                            $str_var_no_quote = substr($val[1], 1);
//                            $str_index        = array_push($str_var_str, $str_var_no_quote) - 1;
//                            //$str_var_index++
//                            $val[1]                   = '${' . $str_global_var . rand_quote(num_hex($options['encode_number'], $str_index)) . '}';
                            $val[1] = '${"' . $str_var_no_quote . '"}';
                            log::info($str_var_no_quote, $val[1]);
                            //$str_var_list[$token_str] = $val[1];
                            break;
                        }*/
                        // 非 function 而且是静态变量
                        if (!$function_stack[$is_function] && $is_static_var) {
                            break;
                        }

                        // use global for this
                        if ($is_function_use) {
                            // find in function params
                            $_is_use_find = 0;
                            foreach ($function_var_list as $_func_var_index => $_func_var_list) {
                                if (isset($_func_var_list[$token_str])) {
                                    $val[1]       = $_func_var_list[$token_str];
                                    $_is_use_find = 1;
                                    break;
                                }
                            }
                            if ($_is_use_find) {
                                $use_var_list[$is_function][$token_str] = $val[1];
                                break;
                            }
                            // find in global
                            if (isset($global_vars[$token_str])) {
                                $val[1]                      = $global_vars[$token_str];
                                $use_var_list[0][$token_str] = $val[1];
                            }
                            break;
                        }
                        $is_get_func_var = false;
                        if ($is_static_call) {
                            if (find_last_token($list, $key, array('(', ','))) {
                                $is_get_func_var = 1;
                            } else {
                                break;
                            }
                        }

                        // 定义的静态变量
                        if ($is_static_var && $is_function) {
                            if ($options['encode_var']) {
                                $static_fun_list[$is_function][$token_str] = generate_name($options['encode_var'], $options['encode_var_length']);
                                $val[1]                                    = $static_fun_list[$is_function][$token_str];
                            } else {
                                $static_fun_list[$is_function][$token_str] = $token_str;
                            }
                            break;
                        }

                        $_function_var_list   = &$function_var_list[$is_function];
                        $_use_var_list        = &$use_var_list[$is_function];
                        $_global_use_var_list = &$use_var_list[0];
                        if ($is_global) {
                            if ($is_function) {
                                $_function_var_list[$token_str] = $val[1];
                            } else {
                                $global_vars[$token_str] = $val[1];
                            }
                        } elseif (($is_get_func_var || $is_function) && isset($_function_var_list[$token_str])) {
                            $val[1] = $_function_var_list[$token_str];
                            // in use function variables
                        } elseif (($is_get_func_var || $is_function) && isset($_use_var_list[$token_str])) {
                            $val[1] = $_use_var_list[$token_str];
                            // in use global variables
                        } elseif (($is_get_func_var || $is_function) && isset($_global_use_var_list[$token_str])) {
                            $val[1] = $_global_use_var_list[$token_str];
                        } elseif ($is_function && isset($static_fun_list[$is_function][$token_str])) {
                            $val[1] = $static_fun_list[$is_function][$token_str];
                        } elseif ($is_get_func_var || $is_function) {
                            if ($options['encode_var']) {
                                $val[1] = generate_name($options['encode_var'], $options['encode_var_length']);
                            }
                            $_function_var_list[$token_str] = $val[1];
                        }
                    }
                    $last_space = true;
                    break;
                case T_CONSTANT_ENCAPSED_STRING:
                    if ($is_static_var || $is_interface == 1) {
                        // skip static var
                    } else if ($function_var_close && $options['encode_str']) {
                        if (!isset($str_var_list[$token_str])) {
                            // todo : eval performance
                            //$val_no_quote = substr($val[1], 1, -1);
                            //$val_no_quote = stripslashes($val_no_quote);

                            $eval_str = '$_GET["____"]=' . $val[1] . ';';
                            eval($eval_str);
                            $val[1] = $_GET["____"];
                            //assert($val_no_quote == $val[1], $val_no_quote . '!=' . $val[1]);
                            //$val[1] = $val_no_quote;

                            // add list
                            $str_index = array_push($str_var_str, $val[1]) - 1;
                            //$str_var_index++
                            $val[1]                   = $str_global_var . rand_quote(num_hex($options['encode_number'], $str_index));
                            $str_var_list[$token_str] = $val[1];
                        } else {
                            $val[1] = $str_var_list[$token_str];
                        }
                    }
                    //
                    $last_space = false;
                    break;
                case T_GLOBAL:
                    $is_global = true;
                    //$val[1] = ($last_space ? '' : ' ' ).$val[1]. ' ';
                    break;
                case T_CATCH:
                    $is_catch = true;
                    break;
                case T_CLASS:
                    $last_space  = false;
                    $is_global   = false;
                    $is_class    = 1;
                    $class_name  = array();
                    $is_function = 0;
                    // can skip
                    /*
                    $is_function = false;
                    $is_static_var = false;
                    $is_string_var = false;
                    $function_var_close = 1;
                    */
                    $last_space = false;
                    break;
                case T_THROW:
                    $is_throw   = true;
                    $val[1]     = ($last_space ? '' : ' ') . $val[1];
                    $last_space = false;
                    break;
                case T_IF:
                    $is_if       = true;
                    $is_short_if = 1;
                    break;
                case T_FUNCTION:
                    $is_global = false;
                    // first deep function
                    if (!$is_function) {
                        $is_function          = 1;
                        $function_var_list    = array(
                            1 => array()
                        );
                        $function_start_point = array(
                            1 => $key,
                        );
                        $use_var_list         = array(
                            1 => array(),
                        );
                        $static_fun_list      = array(
                            1 => array(),
                        );
                    } else {
                        $function_var_list[++$is_function]  = array();
                        $use_var_list[$is_function]         = array();
                        $function_start_point[$is_function] = $key;
                    }
                    $function_var_close = false;
                    $is_static_var      = false;
                    $val[1]             = ($last_space ? '' : ' ') . $val[1];
                    $last_space         = false;
                    break;
                // detect class static use this
                case T_STRING:
                    if ($is_function && !$function_var_close) {
                        break;
                    }
                    if ($is_namespace == 1 && !$namespace_name) {
                        $namespace_name = $val[1];
                        if ($namespace_name) {
                            $namespace_name = '\\' . $namespace_name . '\\';
                        }
                        break;
                    }
                    // get class name
                    if ($options['ob_class'] && $is_class == 1 && !$class_name) {
                        if (!$is_abstract_class) {
                            $class_name = array(
                                'alias' => generate_name($options['ob_class'], $options['encode_var_length'], 0),
                                'name'  => $val[1],
                            );
                            $val[1]     = $class_name['alias'];
                            break;
                        }
                    }
                    // is catch
                    if ($is_string_var || $is_interface == 1 || $is_static_var || $is_catch || $is_throw /*|| $is_ns_separator*/) {
                        break;
                    }
                    $lower_token = strtolower($val[1]);
                    // skip boolean
                    if ($lower_token == 'true') {
                        $val[1] = '!0';
                        break;
                    }
                    if ($lower_token == 'false') {
                        $val[1] = '!1';
                        break;
                    }
                    if (in_array($lower_token, array('null', 'self', 'parent'))) {
                        break;
                    }
                    $is_ob = $options['ob_call'];
                    // check ob call
                    if ($is_ob_array && in_array($lower_token, $options['ob_call'])) {
                        $is_ob = true;
                    }
                    // wont ob call
                    if (!$is_ob) {
                        break;
                    }
                    //log::info('find1', $val[1]);
                    if ($is_class == 1 || $is_namespace == 1) {
                        break;
                    }
                    if (find_last_token($list, $key, array('class', 'namespace', 'extends', 'implements'))) {
                        break;
                    }
                    //namespace
                    if ($list[$key + 1][1] == '\\' || $list[$key - 1]['content'] == '\\') {
                        break;
                    }
                    //log::info('find2', $is_ob, find_last_token($list, $key, array('->', '::')), $val[1]);
                    // skip object call or static call
                    $last_is_call = find_last_token($list, $key, array('->', '::'));
                    if ($is_ob && $last_is_call) {
                        // only method encode
                        if (!find_next_token($list, $key, array('('))) {
                            break;
                        }/*
                        if (find_last_token($list, $key - 1, array('self'))) {
                            break;
                        }*/
                        $pattern_str_var = '%s';
                        $remove_dollar   = 1;
                        if (find_next_token($list, $key, array('['))) {
                            $pattern_str_var = '{%s}';
                            $remove_dollar   = 0;
                        }
                        if (isset($str_var_list['__call' . $token_str])) {
                            $val[1] = $str_var_list['__call' . $token_str];
                            break;
                        }
                        // add list
                        $str_index = array_push($str_var_str, $val[1]) - 1;
                        if (!$is_quote) {
                            $remove_dollar = $remove_dollar ? substr($str_global_var, 1) : $str_global_var;
                            if ($php_version == 7) {
                                $val[1] = '{$' . $remove_dollar . rand_quote(num_hex($options['encode_number'], $str_index)) . '}';
                            } else {
                                $val[1] = '$' . $remove_dollar . rand_quote(num_hex($options['encode_number'], $str_index)) . '';
                            }
                            //log::info('var', $val[1]);
                            $str_var_list['__call' . $token_str] = sprintf($pattern_str_var, $val[1]);
                        } else {
                            $str_var_list['__call' . $token_str] = $val[1];
                        }
                        //$val[1] = '${"'..'"}';
                        //print_r($li);exit;
                        break;
                    }
                    // check in cache for performance
                    if (isset($str_var_list[$token_str])) {
                        $val[1] = $str_var_list[$token_str];
                        break;
                    }
                    if ($is_ob) {
                        $next_is_static = find_next_token($list, $key, array('.', ',', ')', ';', '+',
                                                                             '-', '/', '%', '&', '|', ':',//三元操作符
                                                                             '>>', '!=', '!==', '==',
                                                                             '>=', '<=', '!==', '<>', '^', '?>', '::',
                                                                             '&&', '||', '[',//support new php const array
                                                                             'and', 'or', 'xor', '?'));
                        // is constant
                        if ($next_is_static) {
                            break;
                        }
                        // is namespace /
                        if (!isset($str_var_list[$token_str])) {
                            // add list
                            $str_index = array_push($str_var_str, $val[1]) - 1;
                            if (!$is_quote) {
                                $is_str_defined           = get_defined($val[1]);
                                $val[1]                   = $str_global_var . rand_quote(num_hex($options['encode_number'], $str_index));
                                $str_var_list[$token_str] = sprintf($is_str_defined ? 'constant(%s)' : '%s', $val[1]);
                            } else {
                                $str_var_list[$token_str] = $val[1];
                            }
                        } else {
                            $val[1] = $str_var_list[$token_str];
                        }
                    }
                    break;
                case T_DOUBLE_COLON:
                    $is_static_call = 1;
                    break;
                case T_USE:
                    if ($is_function) {
                        $is_function_use = true;
                    }
                    $val[1] = ($last_space ? '' : ' ') . trim($val[1]) . ' ';
                    !$last_space && $last_space = true;
                    break;
                case T_INSTANCEOF:
                case T_AS:
                    $val[1] = ' ' . trim($val[1]) . ' ';
                    !$last_space && $last_space = true;
                    $is_static_call && $is_static_call = 0;
                    break;
                //过滤各种PHP注释
                case T_COMMENT:
                case T_DOC_COMMENT:
                    $val[1] = $options['remove_comment'] ? '' : $val[1];
                    break;
                //case T_DNUMBER:
                // float
                case T_LNUMBER:
                    $val[1] = num_hex($options['encode_number'], $val[1]);
                    break;
                case T_IS_NOT_EQUAL:
                case T_IS_GREATER_OR_EQUAL:
                case T_IS_EQUAL:
                case T_IS_IDENTICAL:
                case T_IS_NOT_IDENTICAL:
                case T_IS_SMALLER_OR_EQUAL:
                case T_DOUBLE_ARROW:
                    $val[1] = trim($val[1]);
                    break;
                // html compress
                case T_INLINE_HTML:
                    if ($options['encode_html'] && strlen($val[1]) > 32) {
                        if (!isset($str_var_list[$token_str])) {
                            // add list
                            $str_index = array_push($str_var_str, $val[1]) - 1;
                            //$str_var_index++
                            $val[1]                   = $str_global_var . rand_quote(num_hex($options['encode_number'], $str_index));
                            $str_var_list[$token_str] = $val[1];
                        } else {
                            $val[1] = $str_var_list[$token_str];
                        }

                        $val[1] = '<' . '?=' . $val[1] . ';?' . '>';
                        /*
                        $is_ob = find_ob_function($options, 'print_r');
                        //$echo_func = get_func_param($is_ob, $options['encode_call'], $str_index, $str_var_list, $str_var_str, $str_global_var, 'print_r');
                        $echo_func = get_func_param($is_ob, $options['encode_call'], $str_index, $str_var_list, $str_var_str,  $str_global_var, 'print_r');
                        $is_ob = find_ob_function($options, 'gzinflate');
                        $gz_func = get_func_param($is_ob, $options['encode_call'], $str_index, $str_var_list, $str_var_str, $str_global_var, 'gzinflate');
                        $is_ob = find_ob_function($options, 'substr');
                        $sub_func = get_func_param($is_ob, $options['encode_call'], $str_index, $str_var_list, $str_var_str,  $str_global_var, 'substr');
                        */
                        //$val[1] = '<' . '?php ' . $echo_func . '(' . output_gz($val[1], $gz_func, $sub_func, $options['encode_number'], $options['encode_gz']) . ');?' . '>';

                    }
                    break;
                case T_PRIVATE:
                case T_PROTECTED:
                case T_PUBLIC:
                case T_VAR:
                case T_CONST:
                    $is_static_var = true;
                    $val[1]        = ($last_space ? '' : ' ') . trim($val[1]) . ' ';
                    $last_space    = true;
                    break;
                case T_STATIC:
                    $is_static_var = true;
                    $val[1]        = ($last_space ? '' : ' ') . trim($val[1]) . ' ';
                    $last_space    = true;
                    break;
                // { in string variable
                //	complex variable parsed syntax
                case T_CURLY_OPEN:
                    $is_string_var = true;
                    $is_class && $is_class++;
                    $is_namespace && $is_namespace++;
                    break;
                case T_EXTENDS:
                case T_IMPLEMENTS:
                    $val[1]     = ' ' . trim($val[1]) . ' ';
                    $last_space = true;
                    break;
                case T_FOR:
                    $is_for = 2;
                case T_ELSEIF:
                    $is_elseif = 1;
                    break;
                case T_LOGICAL_AND:
                    $val[1]     = '&&';
                    $last_space = true;
                    $is_static_call && $is_static_call = 0;
                    break;
                case T_LOGICAL_OR:
                    $val[1]     = '||';
                    $last_space = true;
                    $is_static_call && $is_static_call = 0;
                    break;
                case T_OPEN_TAG_WITH_ECHO:
                    break;
                case T_END_HEREDOC:
                    $heredoc_end = true;
                    break;
                default:
                    $last_space = false;
                    break;
            }
            if (is_numeric($val[1]) || $val[1]) {
                $val = array(
                    'token_name' => $token_name,
                    'content'    => $val[1],
                    'line_num'   => $val[2],
                );
                if ($options['ob_function']) {
                    if (!$is_class && $is_function && !$function_var_close) {
                        if ($token_idx == T_FUNCTION) {
                            continue;
                        } else if ($token_idx == T_STRING) {
                            $function_alias[] = generate_name($options['ob_function'], $options['ob_function_length'], 0);
                        } else {
                            $function_alias[] = $val['content'];
                        }
                    }
                }
            }
        } else {
            switch ($val) {
                case ';':
                    $trim_last = 1;
                    if ($heredoc_end) {
                        $heredoc_end = false;
                        $val         .= "\r\n";
                    }
                    if (!$is_string_var && !$is_abstract_function
                        && !$is_interface
                        && !$is_for && !$is_short_if
                        && !$is_elseif && $is_function == 1
                    ) {
                        $mess_code = '';
                        if ($options['insert_mess'] && rand(0, $options['insert_mess'])) {
                            $mess_code = generate_name(2, rand(10, 100), 0, 0, chr(144)) . ';';
                        }
                        $val .= ($options['new_line'] ? "\r\n" : "") . $mess_code;
                    }
                    $is_short_if == 1 && $is_short_if--;
                    $is_elseif == 1 && $is_elseif--;
                    $is_for && $is_for--;
                    if ($is_static_var) {
                        $is_static_var = false;
                    }
                    if ($is_function && $is_global) {
                        $is_global = false;
                    }
                    // is abstract
                    if ($is_abstract_function && $is_function) {
                        $is_function    = 0;
                        $function_stack = array();
                    }
                    $is_abstract_function && $is_abstract_function = 0;
                    $is_static_call && $is_static_call = 0;
                    $is_namespace && $is_namespace = 0;
                    $is_throw && $is_throw = 0;
                    $is_ns_separator && $is_ns_separator--;
                    break;
                case '{':
                    $trim_last = 1;
                    if ($is_catch) {
                        $is_catch = false;
                    }
                    $is_elseif && $is_elseif++;
                    $is_short_if && $is_short_if++;
                    /*
                    if (!$is_abstract_function && !$is_interface && !$is_class && $is_function == 1 && $options['insert_mess'] && rand(0, $options['insert_mess'])) {
                        $val .= ($options['new_line'] ? "\r\n" : "") . generate_name(2, rand(10, 50), 0, 0) . ';';
                    }
                    */
                    $is_interface && $is_interface++;
                    $is_class && $is_class++;
                    $is_abstract_function && $is_abstract_function = 0;
                    if (!$is_string_var) {
                        $is_function && $function_stack[$is_function]++;
                        // get start point
                        if ($is_function && $function_stack[$is_function] == 1) {
                            $function_start_point[$is_function] = $key;
                        }
                        $is_class && $class_stack++;
                    }
                    if ($is_static_var) {
                        $is_static_var = false;
                    }
                    $is_static_call && $is_static_call = 0;
                    $is_namespace && $is_namespace--;
                    break;
                case '}':
                    $trim_last = 1;
                    $is_namespace && $is_namespace--;
                    $is_elseif && $is_elseif--;
                    $is_short_if && $is_short_if--;
                    if ($is_namespace == 1) {
                        $is_namespace   = 0;
                        $namespace_name = '';
                    }
                    $is_interface && $is_interface--;
                    if ($is_class && --$is_class == 1 && $class_name) {
                        if (!$is_abstract_class) {
                            $_class_alias = get_str_list($str_var_list, $str_var_str, $namespace_name . $class_name['alias'], $str_global_var, $options);
                            $_class_name  = get_str_list($str_var_list, $str_var_str, $namespace_name . $class_name['name'], $str_global_var, $options);
                            $is_ob        = find_ob_function($options, 'class_alias');
                            $gz_func      = get_func_param($is_ob, $options['encode_call'], $str_index, $str_var_list, $str_var_str, $str_global_var, 'class_alias');
                            $val          .= $gz_func . "({$_class_alias},{$_class_name},0);print_R({$_class_alias});print_R({$_class_name});";
                            // reset class detect
                            $class_name        = array();
                            $is_class          = 0;
                            $is_abstract_class = 0;
                        }
                    } else if ($is_string_var) {
                        $is_string_var = false;
                    } else {
                        if ($is_function && --$function_stack[$is_function] === 0) {
                            // set static function variable empty
                            $static_fun_list[$is_function] = array();
                            // find all of variables contains global;
                            $index_key = $key;
                            //print_R(array_splice($list, 0, $key));exit;
                            //
                            // 绑定全局变量
                            $find_global_var = 0;
                            $_function_stack = array(
                                0 => 1,
                            );
                            $_function_index = 0;
                            //$lower_global_var = strtolower($str_global_var);
                            $str_global_var_same_quote = strtr($str_global_var, $same_quotes);
                            while ($options['encode_var'] && $index_key-- > -1) {
                                $token     = $list[$index_key];
                                $token_str = isset($token['content']) ? strtolower(rtrim($token['content'])) : $token;
                                $token_var = substr($token['content'], 0, $len_global_var);
                                if (strtr($token_var, $same_quotes) == $str_global_var_same_quote) {
                                    $find_global_var++;
                                }
                                switch ($token_str) {
                                    case 'function':
                                        //$_function_stack[++$_function_index] = 0;
                                        break;
                                    case '{':
                                        if (--$_function_stack[$_function_index] == 0) {
                                            //$_function_index--;
                                        }
                                        break;
                                    case '}':
                                        $_function_stack[$_function_index]++;
                                        break;
                                }
                                if ($_function_stack[0] == 0 && $token_str == 'function') {
                                    // 少于2次引用，不使用别名模式
                                    if ($find_global_var < 2) {
                                        break;
                                    }
                                    $function_global_var = generate_name($options['encode_var'], $options['encode_var_length']);
                                    // 这里不能插入了，如果文件太大，导致list太大
                                    // 内存复制太慢，插入list中太慢，
                                    // 新建一个 list 在生成新文件的时候插入
                                    $_function_start_pos               = $function_start_point[$is_function] + 1;
                                    $insert_list[$_function_start_pos] = $function_global_var . '=&' . $str_global_var . ';';
                                    // replace all variable form new var
                                    $_function_stack = array(
                                        0 => 1,
                                    );
                                    $_function_index = 0;
                                    for ($rollback_key = $index_key + 2; $rollback_key < $key; $rollback_key++) {
                                        $token = &$list[$rollback_key];
                                        /*if ($is_function == 0) {
                                            log::info($token);
                                        }*/
                                        // for anonymous function
                                        switch (strtolower(trim($token['content']))) {
                                            case 'function':
                                                $_function_stack[++$_function_index] = 0;
                                                break;
                                            case '{':
                                                $_function_stack[$_function_index]++;
                                                break;
                                            case '}':
                                                if (--$_function_stack[$_function_index] == 0) {
                                                    $_function_index--;
                                                }
                                                break;
                                        }
                                        if ($_function_index == 0 && in_array($token['token_name'], $allow_modify_variables)) {
                                            $is_modify  = substr($token['content'], 0, 3) == '<?=' ? 4 : 1;
                                            $_var_start = substr($token['content'], $is_modify - 1, $len_global_var);
                                            if (strtr($_var_start, $same_quotes) == $str_global_var_same_quote) {
                                                $token['content'] = substr_replace($token['content'], $function_global_var, $is_modify - 1, $len_global_var);
                                            }
                                        }
                                    }
                                    unset($token);
                                    break;
                                }
                            }
                            $is_function--;
                        }
                        if ($is_class && --$class_stack == 0) {
                            $is_class = false;
                        }
                    }

                    if (!$is_function && !$is_class) {
                        $is_global = true;
                    }
                    break;
                case '(':
                    $trim_last = 1;
                    if ($is_static_var && find_left_quote($list, $key, 1)) {
                        $is_static_var = false;
                    }
                    if ($options['ob_function']) {
                        if (!$is_class && $is_function && !$function_var_close) {
                            $function_alias[] = $val;
                        }
                    }
                    $is_static_call && $is_static_call = 0;
                    break;
                case ')':
                    $trim_last = 1;
                    if ($is_class) {
                        if ($is_function && !$function_var_close && find_left_quote($list, $key)) {
                            $function_var_close = 1;
                        } else {

                        }
                        break;
                    }
                    if ($is_if) {
                        if (find_left_quote($list, $key)) {
                            $is_if = 0;
                        }
                        break;
                    }
                    // function () use()
                    $is_function_use && $is_function_use = 0;
                    if (!$function_var_close && !find_left_quote($list, $key)) {
                        $options['ob_function'] && $function_alias[] = ')';
                        break;
                    }
                    $function_var_close = 1;
                    if (!$options['ob_function']) {
                        break;
                    }
                    if (!$function_alias) {
                        break;
                    }
                    $function_alias[] = ')';
                    //print_r($function_alias);
                    $old_function = array();
                    $index_key    = $key;
                    $token        = 0;
                    while ($index_key-- > -1) {
                        $token          = $list[$index_key];
                        $old_function[] = $token['content'];
                        if (trim(strtolower($token['content'])) == 'function') {
                            break;
                        }
                    }
                    if (!$token) {
                        break;
                    }
                    // 匿名方法
                    if (!trim($function_alias[0])) {
                        break;
                    }
                    $func_unset = 0;
                    foreach ($function_alias as $func_index => $func_str) {
                        switch (strtolower($func_str)) {
                            case '=':
                                $func_unset = 1;
                                break;
                            case 'array':
                                if ($func_unset > 0) {
                                    $func_unset = 0;
                                }
                                $func_unset -= 2;
                                break;
                            case ',':
                                if ($func_unset == 1) {
                                    $func_unset = 0;
                                } elseif ($func_unset > 0) {
                                    $func_unset = 1;
                                }
                                break;
                            case ')':
                                if ($func_unset < 0) {
                                    $func_unset += 2;
                                    if ($func_unset == 0) {
                                        unset($function_alias[$func_index]);
                                    }
                                } else {
                                    $func_unset = 0;
                                }
                                // $func_unset = 0;
                                break;
                        }
                        if ($func_unset) {
                            unset($function_alias[$func_index]);
                        }
                    }
                    $function_new = implode('', $function_alias);
                    $old_function = array_reverse($old_function);

                    $old_function                = implode('', $old_function) . '){return ' . $function_new . ';}';
                    $list[$index_key]['content'] = $old_function;
                    // unset all function
                    for ($i = $index_key + 1; $i < $key; $i++) {
                        $list[$i]['content'] = '';
                    }
                    $val            = 'function ' . ltrim($function_new);
                    $function_alias = array();
                    break;
                case '[':
                case '.':
                    $is_static_call && $is_static_call = 0;
                    break;
                case '=':
                case ',':
                    $is_static_call && $is_static_call = 0;
                    if ($options['ob_function']) {
                        if (!$is_class && $is_function && !$function_var_close) {
                            $function_alias[] = $val;
                        }
                    }
                    $trim_last = 1;
                    break;
                case "\r":
                case "\n":
                    $val = ' ';
                    break;
                case '"':
                    $is_quote = $is_quote ? 0 : 1;
                    break;
            }

            if ($val) {
                $val = array(
                    'content' => $val,
                );
            }
            $last_space = true;
        }
        if ($trim_last && !$options['new_line']) {
            if (isset($list[$key - 1]['content'])) {
                $list[$key - 1]['content'] = rtrim($list[$key - 1]['content']);
            }
        }
        /*
        if ($is_debug) {
            $end_time  = microtime_float();
            $used_time = ($end_time - $start_time) * 1000;
            //log::info($used_time);
            if ($used_time > 30) {
                log::info($used_time, $val);
            }
        }
        */
    }

    $vars = '';
    /*$arr1 = explode($str_var_char, $str_var_str);
    $arr2 = explode($str_var_char, gzinflate(substr(gzencode($str_var_str), 10, -8)));
    print_R(array_diff($arr1, $arr2));
    */

    $comment = $options['comment'] ? '/* ' . $options['comment'] . ' */' : '';

    if ($str_var_str) {
        // delete last char
        //substr($str_var_str, 0, 0 - strlen($str_var_char));
        $str_var_str = implode($str_var_char, $str_var_str);
        //echo $str_var_str;exit;

        $vars = 'error_reporting(E_ALL^E_NOTICE);';
        $vars .= 'define(\'' . $str_define_name . '\', \'' . $str_var_name . '\');';
        $vars .= $options['insert_mess'] ? generate_name(2, rand(100, 200), 0, 0) . ';' : '';
        $vars .= $str_global . '[' . $str_define_name . '] = explode(\'' . $str_var_char . '\', ' . output_gz($str_var_str, 'gzinflate', 'substr', $options['encode_number'], $options['encode_gz']) . ');';
        $vars .= $options['insert_mess'] ? generate_name(2, rand(100, 200), 0, 0) . ';' : '';
        //$vars .= '?'.'>';
    }
    $str           = '';
    $is_namespace  = false;
    $namespace_str = '';
    foreach ($list as $key => $c) {
        if (isset($insert_list[$key])) {
            $str .= $insert_list[$key];
        }
        $str .= isset($c['content']) ? $c['content'] : '';
        if ($c['token_name'] == 'T_NAMESPACE') {
            $is_namespace = true;
        } else if ($is_namespace) {
            if ($is_namespace && (trim($c['content']) == ';' || trim($c['content']) == '{')) {
                $str          .= $vars;
                $vars         = '';
                $is_namespace = false;
            }
        }
    }
    if ($vars) {
        $vars = '<?php ' . $vars . '?>';
    }
    if ($comment) {
        $namespace_str = '<?php ' . $comment . '?>' . $namespace_str;
    }
    $str = $namespace_str . $vars . $str;
    $str = str_replace('?' . '><?' . 'php', '', $str);
    return $str;
}


/**
 * cut string from $start to $end
 *
 * @param        $html
 * @param string $start
 * @param string $end
 *
 * @return string
 */
function enphp_cut_str($html, $start = '', $end = '') {
    if ($start) {
        $html = stristr($html, $start, false);
        $html = substr($html, strlen($start));
    }
    $end && $html = stristr($html, $end, true);
    return $html;
}

//
/*
*/
/**
 * mask match string:
 *
 * spider::mask_match('123abc123', '123(*)123') = abc
 * spider::mask_match('abc123', '(*)123') = abc
 * spider::mask_match('123abcabc', '(*)abc') = 123
 * spider::mask_match('123abcdef', '(*)abc', true) = 123abc
 *
 * @param            $html
 * @param            $pattern
 * @param bool|false $returnfull
 *
 * @return string
 */
function enphp_mask_match($html, $pattern, $returnfull = false) {
    $part = explode('(*)', $pattern);
    if (count($part) == 1) {
        return '';
    } else {
        if ($part[0] && $part[1]) {
            $res = enphp_cut_str($html, $part[0], $part[1]);
            if ($res) {
                return $returnfull ? $part[0] . $res . $part[1] : $res;
            }
        } else {
            //pattern=xxx(*)
            if ($part[0]) {
                if (strpos($html, $part[0]) !== false) {
                    $html = explode($part[0], $html);
                    if ($html[1]) {
                        return $returnfull ? $part[0] . $html[1] : $html[1];
                    }
                }
            } elseif ($part[1]) {
                //pattern=(*)xxx
                if (strpos($html, $part[1]) !== false) {
                    $html = explode($part[1], $html);
                    if ($html[0]) {
                        return $returnfull ? $html[0] . $part[1] : $html[0];
                    }
                }
            }
        }
        return '';
    }
}

function format_code(&$source) {
    $patterns = array(
        '#<hi' . 'de>(*)#</hi' . 'de>'       => '',
        '/*<hi' . 'de>*/(*)/*</hi' . 'de>*/' => '',
    );
    // replace hide block
    foreach ($patterns as $pattern => $replace) {
        $search = enphp_mask_match($source, $pattern, true);
        $source = str_replace($search, $replace, $source);
    }

    $encode_str         = '/*<en' . 'code>*/';
    $encode_str_len     = strlen($encode_str);
    $encode_str_end     = '/*</en' . 'code>*/';
    $encode_str_end_len = strlen($encode_str_end);
    while (strpos($source, $encode_str) !== false) {
        $start_pos = strpos($source, $encode_str);
        $end_pos   = strpos($source, $encode_str_end);
        $end_pos   = $end_pos - $encode_str_end_len - $start_pos + 1;
        $enstr     = substr($source, $start_pos + $encode_str_len, $end_pos);
        $enstr     = trim($enstr);
        if (is_numeric($enstr)) {
            $str = encode_num($enstr);
        } else if ($enstr[0] != substr($enstr, -1) || !in_array($enstr[0], array('"', "'"))) {
            $str = $enstr;
        } else {
            $str = '';
            try {
                $str = encode_str(parse_string_var($enstr));
            } catch (Exception $e) {
                continue;
            }
        }
        $source = substr_replace($source, ($str), $start_pos, $end_pos + $encode_str_end_len * 2 - 1);
    }
}


function encode_num($s, $rand = 0) {
    $n1 = rand(1, 100);
    $n2 = rand(2, 200);
    $n3 = rand(300, 500);
    switch (rand(1, 4)) {
        case 1:
            $n1 = rand(10, 100);
            $n2 = rand(2, 20);
            return '(' . ($s * $n1 - $n2) . '+' . $n2 . ')/' . $n1;
            break;
        case 2:
            return ($s - $n2 * $n1) . '+' . $n2 . '*' . $n1;
            break;
        case 3:
            return ($s + $n3 - $n2 * $n1) . '-' . $n3 . '+' . $n2 . '*' . $n1;
            break;
        case 4:
            return ($s - $n3 - $n2 * $n1) . '+' . $n3 . '+' . $n2 . '*' . $n1;
            break;
    }
}

/**
 * encode str
 *
 * @param $s
 */
function encode_str($s, $rand = 0) {
    switch (rand(1, 4 + $rand)) {
        case 1:
            $s = base64_encode($s);
            $s = strtr($s, array('=' => ''));
            return 'base64_decode(\'' . $s . '\')';
            break;
        case 2:
            $s = base64_encode($s);
            $s = strtr($s, array('=' => ''));
            return 'base64_decode(\'' . $s . '\')';
            break;
        case 3:
            $s = base64_encode(gzencode($s));
            $s = strtr($s, array('=' => ''));
            return 'gzinflate(substr(base64_decode(\'' . $s . '\'), 10, -8))';
            break;
        case 4:
            $s = str_rot13(base64_encode($s));
            $s = strtr($s, array('=' => ''));
            return 'base64_decode(str_rot13(\'' . $s . '\'))';
            break;
    }
}

/**
 * @param $str_var_list
 * @param $token_str
 * @param $str_global_var
 * @param $options
 */
function get_str_list(&$str_var_list, &$str_var_str, $token_str, $str_global_var, &$options) {
    if (!isset($str_var_list[$token_str])) {
        // add list
        $str_index                = array_push($str_var_str, $token_str) - 1;
        $is_str_defined           = get_defined($token_str);
        $result                   = $str_global_var . rand_quote(num_hex($options['encode_number'], $str_index));
        $str_var_list[$token_str] = sprintf($is_str_defined ? 'constant(\'%s\')' : '%s', $result);
    } else {
        $result = $str_var_list[$token_str];
    }
    return $result;
}

/**
 * microtime float
 *
 * @return float
 */
function microtime_float() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

/**
 * insert array in array by position
 *
 * @param $list
 * @param $position
 * @param $array
 *
 * @return array
 */
function array_insert(&$list, $position, $array) {
    log::info('insertStart');
    array_splice($list, $position + 1, 0, $array);
    log::info('insertOver');
}

/**
 * check utf8 bom
 *
 * @param $content
 */
function check_bom(&$content) {
    $charset[1] = substr($content, 0, 1);
    $charset[2] = substr($content, 1, 1);
    $charset[3] = substr($content, 2, 1);
    if (ord($charset[1]) == 239 && ord($charset[2]) == 187 && ord($charset[3]) == 191) {
        $content = substr($content, 3);
    }
}

/**
 * find ob function
 *
 * @param $options
 *
 * @return int
 */
function find_ob_function($options, $func) {
    $is_ob = $options['ob_call'];
    if (is_array($options['ob_call']) && in_array($func, $options['ob_call'])) {
        $is_ob = 1;
    }
    return $is_ob;
}

/**
 * find last token is keyword
 *
 * @param $list
 * @param $index
 * @param $keywords
 *
 * @return int
 */
function find_last_token(&$list, $index, $keywords) {
    while (--$index && $index > -1) {
        $keyword = strtolower(trim($list[$index]['content']));
        if (!$keyword) {
            continue;
        }
        if (in_array($keyword, $keywords)) {
            return 1;
        } else {
            return 0;
        }
    }
}

/**
 * find next token
 *
 * @param $list
 * @param $index
 * @param $keywords
 *
 * @return int
 */
function find_next_token(&$list, $index, $keywords) {
    $len = count($list);
    while (++$index && $index < $len) {
        $str     = isset($list[$index]['content']) ? $list[$index]['content'] : (isset($list[$index][1]) ? $list[$index][1] : $list[$index]);
        $keyword = trim($str);
        if (!$keyword) {
            continue;
        }
        if (in_array(strtolower($keyword), $keywords)) {
            return 1;
        } else {
            return 0;
        }
    }
}

function find_next_is_not_statment(&$list, $index, $keywords) {
    $len    = count($list);
    $is_var = 0;
    while (++$index && $index < $len) {
        $str     = isset($list[$index]['content']) ? $list[$index]['content'] : (isset($list[$index][1]) ? $list[$index][1] : $list[$index]);
        $keyword = trim($str);
        if (!$keyword) {
            continue;
        }
        log::info('next_statment', $keyword);
        if (!in_array(strtolower($keyword), $keywords)) {
            return 1;
        } else {
            return 0;
        }
    }
}

/**
 * find quote left or right
 *
 * @param     $list
 * @param     $index
 * @param int $left
 *
 * @return int
 */
function find_left_quote(&$list, $index, $left = 0) {
    $right_quote = $left ? 0 : 1;
    $left_quote  = $left;
    $quit        = 0;
    while (--$index && $index > -1) {
        $word = strtolower(trim($list[$index]['content']));
        switch ($word) {
            case 'protected':
            case 'private':
            case 'public':
            case 'static':
            case 'var';
            case 'function':
            case 'if':
            case 'use':
                $quit = 1;
                break;
            case '=':
                //return ($right_quote - $left_quote == 1) ? 1 : 0;
                break;
            case '(':
                $left_quote++;
                break;
            case ')':
                $right_quote++;
                break;
        }
        if ($quit) {
            break;
        }
    }
    if ($right_quote - $left_quote == 0) {
        return 1;
    } else {
        return 0;
    }
}

/**
 * insert new param
 *
 * @param $is_ob
 * @param $encode
 * @param $str_index
 * @param $str_var_list
 * @param $str_var_str
 * @param $str_var_char
 * @param $str_global_var
 * @param $func
 *
 * @return string
 */
function get_func_param($is_ob, $encode, &$str_index, &$str_var_list, &$str_var_str, &$str_global_var, $func) {
    //return $func;
    if (!$is_ob) {
        return $func;
    }
    if (!isset($str_var_list[$func])) {
        // add list
        $str_index           = array_push($str_var_str, $func) - 1;
        $result              = $str_global_var . rand_quote(num_hex($encode, $str_index++));
        $str_var_list[$func] = $result;
    } else {
        $result = $str_var_list[$func];
    }
    return $result;
}

/**
 * generate variables name
 *
 * @param int $encode
 * @param int $len
 * @param int $add_dollar
 * @param int $check_exists
 *
 * @return mixed|string
 */
function generate_name($encode = 1, $len = 4, $add_dollar = 1, $check_exists = 1, $pre = '') {
    global $gen_count;
    static $exists_name = array();
    static $exists_index = 0;
    $varname = '';
    while (true) {
        $gen_count++;
        $varname = '';
        if ($encode == 2) {
            foreach (range(1, rand(1, $len)) as $i) {
                $varname .= chr(rand(128, 254));
            }
        } else if ($encode == 1) {
            $exists_index++;
            $varname .= decbin($exists_index);
            $varname = str_replace('1', 'O', $varname);
        } else {
            $exists_index++;
            $zero_length = $len - strlen($exists_index);
            $varname     .= 'v' . ($zero_length > 0 ? str_repeat(0, rand(1, $zero_length)) : '') . $exists_index;
        }
        $varname = $pre . $varname;
        if (!$check_exists) {
            break;
        }
        // is exists variable
        if (isset($exists_name[$varname])) {
            continue;
        }
        // add array
        $exists_name[$varname] = 1;
        break;
    }

    return ($add_dollar ? '$' : '') . $varname;
}

/** strip str
 *
 * @param $str
 *
 * @return mixed
 */
function strip_str($str) {
    $replaces = array(
        '\\' => '\\\\',
        '\'' => '\\\'',
    );
    foreach ($replaces as $from => $to) {
        $str = str_replace($from, $to, $str);
    }
    return $str;
}

/**
 * 随机括号
 *
 * @param $str
 *
 * @return string
 */
function rand_quote($str) {
    static $index = 0;
    return $index++ % 2 == 1 ? '{' . $str . '}' : '[' . $str . ']';
}

/**
 *  out put gzencode
 *
 * @param $str
 * @param $gz_func
 * @param $sub_func
 * @param $encode_number
 *
 * @return string
 */
function output_gz($str, $gz_func, $sub_func, $encode_number, $is_gz = 0) {
    if (!$is_gz) {
        return '\'' . strip_str($str) . '\'';
    } else {
        return $gz_func . '(' . $sub_func . '(\'' . strip_str(gzencode($str)) . '\',' . num_hex($encode_number, 10) . ', -8))';
    }
}

/**
 * hex for number
 *
 * @param $encode
 * @param $num
 *
 * @return string
 */
function num_hex($encode, $num) {
    if ($encode == 1) {
        if (strpos($num, '0') === 0) {
            return $num;
        }
        if (strpos($num, '0x') === 0) {
            $num = base_convert($num, 16, 10);
        }
        $repeat = ($num % 5) + 1;
        $str    = '0x' . str_repeat('0', $repeat) . base_convert($num, 10, 16);
        return $str;
    } else {
        return $num;
    }
}

/**
 * get defined by cache
 *
 * @param $name
 *
 * @return mixed
 */
function get_defined($name) {
    static $define_list = array();
    if (!isset($define_list[$name])) {
        $define_list[$name] = defined($name);
    }
    return $define_list[$name];
}

/**
 * get usedtime
 *
 * @return string
 */
function usedtime() {
    return number_format(microtime(1) - $_SERVER['starttime'], 6) * 1000;
}

/**
 * parse string variable
 *
 * @param $s
 *
 * @return string|void
 */
function parse_string_var($s) {
    $quote        = substr($s, 0, 1);
    $val_no_quote = substr($s, 1, -1);
    if ($quote == '"') {

    } else {

    }
    $val_no_quote = stripslashes($val_no_quote);
    return $val_no_quote;
}

if (!class_exists('log', false)) {
    class log
    {
        /**
         * @var int log file name
         */
        public static $log_file = 0;
        /**
         * @var int|resource log file file pointer
         */
        public static $log_fp = 0;

        /**
         * init to log
         *
         * @param $file
         */
        public static function set_logfile($file) {
            if ($file == 1) {
                $file = 'data/log/' . date('Y-m-d') . '.log';
            }
            self::$log_file = $file;
            self::$log_fp   = fopen($file, 'a+');
        }

        /**
         * alias set log file
         *
         * @param $file
         */
        public static function set_file($file) {
            self::set_logfile($file);
        }

        /**
         * dump variable for log
         *
         * @param $data
         *
         * @return string
         */
        public static function dump_var($data) {
            if (is_array($data)) {
                $str = '';
                foreach ($data as $k => $v) {
                    if (is_array($v)) {
                        $str .= '[' . $k . '=' . self::dump_var($v) . ']';
                    } else {
                        $str .= '[' . $k . '=' . $v . ']';
                    }
                }
                return $str;
            } else {
                return '[' . $data . ']';
            }
        }

        /**
         * log::info($arg1,$arg2....$argn);
         *
         * @param mixed
         */
        public static function info() {
            self::add_log('info', func_get_args(), func_num_args());
        }

        /**
         * log::error($arg1,$arg2....$argn);
         *
         * @param mixed
         */
        public static function error() {
            self::add_log('error', func_get_args(), func_num_args());
            throw new Exception('error');
        }

        /**
         * add log
         *
         * @param $type
         * @param $arg_list
         * @param $arg_count
         */
        private static function add_log($type, $arg_list, $arg_count) {
            $log = '';
            for ($i = 0, $l = $arg_count; $i < $l; $i++) {
                $log .= self::dump_var($arg_list[$i]);
            }
            $log .= '[' . usedtime() . "ms]";
            $log = "[" . date('H:i:s') . "]" . $log . "\r\n";
            if (self::$log_fp) {
                fputs(self::$log_fp, $log);
            }
            if (php_sapi_name() == 'cli') {
                echo $log;
            } else {
                if (isset($_SERVER['log'])) {
                    $_SERVER['log'] = array(
                        'info'  => array(),
                        'error' => array(),
                    );
                }
                $_SERVER['log'][$type][] = $log;
            }
        }
    }

}
/**
 * hex dump
 *
 * @param        $data
 * @param string $newline
 */
function hex_dump($data, $newline = "\n") {
    static $from = '';
    static $to = '';

    static $width = 16; // 每行宽度
    static $pad = '.';
    if ($from === '') {
        for ($i = 0; $i <= 0xFF; $i++) {
            $from .= chr($i);
            $to   .= ($i >= 0x20 && $i <= 0x7E) ? chr($i) : $pad;
        }
    }

    $hex   = str_split(bin2hex($data), $width * 2);
    $chars = str_split(strtr($data, $from, $to), $width);

    $offset = 0;
    foreach ($hex as $i => $line) {
        echo sprintf('%6X', $offset) . ' : ' . implode(' ', str_split($line, 2)) . ' [' . $chars[$i] . ']' . $newline;
        $offset += $width;
    }
}


?>
