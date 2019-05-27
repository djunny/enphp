


# enphp

![LOGO](https://github.com/djunny/enphp/raw/master/logo.png)


```
// 一个开源加密混淆 PHP 代码项目
// a Open Source PHP Code Confusion + Encryption Project
```

## 背景

```
曾经，作者也是商业软件开发者中小将一名，软件总是被人破解，于是花了几个月研究了 EnPHP。
这套项目也有偿提供过给很多人，不过，应该网上存在不少破解了。
项目主要贵在为大家提供一个加密混淆和还原的思路。
// 严禁用于非法用途。
```

## 加密效果

![LOGO](https://github.com/djunny/enphp/raw/master/encode.png)


## 使用方法 
```
include './func_v2.php';
$options = array(
        //混淆方法名 1=字母混淆 2=乱码混淆
        'ob_function'        => 2,
        //混淆函数产生变量最大长度
        'ob_function_length' => 3,
        //混淆函数调用 1=混淆 0=不混淆 或者 array('eval', 'strpos') 为混淆指定方法
        'ob_call'            => 1,
        //随机插入乱码
        'insert_mess'        => 0,
        //混淆函数调用变量产生模式  1=字母混淆 2=乱码混淆
        'encode_call'        => 2,
        //混淆class
        'ob_class'           => 0,
        //混淆变量 方法参数  1=字母混淆 2=乱码混淆
        'encode_var'         => 2,
        //混淆变量最大长度
        'encode_var_length'  => 5,
        //混淆字符串常量  1=字母混淆 2=乱码混淆
        'encode_str'         => 2,
        //混淆字符串常量变量最大长度
        'encode_str_length'  => 3,
        // 混淆html 1=混淆 0=不混淆
        'encode_html'        => 2,
        // 混淆数字 1=混淆为0x00a 0=不混淆
        'encode_number'      => 1,
        // 混淆的字符串 以 gzencode 形式压缩 1=压缩 0=不压缩
        'encode_gz'          => 0,
        // 加换行（增加可阅读性）
        'new_line'           => 1,
        // 移除注释 1=移除 0=保留
        'remove_comment'     => 1,
        // debug
        'debug'              => 1,
        // 重复加密次数，加密次数越多反编译可能性越小，但性能会成倍降低
        'deep'               => 1,
        // PHP 版本
        'php'                => 7,
    );
$file = 'code_test/1.php';
$target_file = 'encoded/2.php';
enphp_file($file, $target_file, $options);
```

## 回归测试脚本：
可以将你要测试的代码放至 code_test 中，运行命令：
```
php code_test.php
```
程序会自动进行回归测试，我也放了一些之前要测试的脚本在里边

P.S.

```
本来，还实现了 goto + xor 变种，不过兼容性和性能有点差，等有时间精力的时候再研究罢...
```

# 一些注意事项

## 如何让 EnPHP 加密强度更高？

1. 将全局逻辑尽量变成类方法，EnPHP 对类加密会有更好的加密混淆效果
2. 对于 class 的变量初始化请放至析构（__construct）方法中
3. 对于多维数组能用数字下标尽量用数字
4. 使用注释加密加强混淆强度


## 在混淆类名时，代码一定要有先后顺序：
```
interface i {
    function init($a, $b);
}

class ii implements i {
    // PHP 中继承的参数名可以不一样
    function init($b, $c) {
        echo $b, $c;
    }
}
```

```
namespace a{
    class b{
    }
    # 正确
    $b = new \a\b();
    # 错误 
    #$b = new b():
}
```

## 使用注释语法加密字符串（支持字符串+数字）：
```
   //格式：/*<encode>*/要二次混淆的内容/*</encode>*/
   $a = /*<encode>*/"明文数据1"/*</encode>*/;
   echo /*<encode>*/2/*</encode>*/;
   print(/*<encode>*/"明文数据3"/*</encode>*/);
```



## 使用注释语法去除代码：
```
   echo 1;
   /*<hide>*/
   echo 2;
   /*</hide>*/
   echo 3;
   //格式：/*<hide>*/要隐藏的代码/*</hide>*/
```
