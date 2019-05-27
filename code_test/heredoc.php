<?php
echo <<<heredoc

heredoc_start 

enphp好像有个bug，heredoc结束符后未换行，报错。

heredoc_end

heredoc;
echo '<br>--------------------分割线------------------<br>';
echo <<<'nowdoc'

nowdoc_start

enphp好像有个bug，heredoc结束符后未换行，报错。

nowdoc_end

nowdoc;
echo '<br>--------------------分割线------------------<br>';

?>