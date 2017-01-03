<?php

spl_autoload_register(function ($class) {
    if ($class) {
        $file = str_replace('\\', '/', $class);
        $file .= '.class.php';
        if (file_exists($file)) {
            include $file;
        }
    }
});

?>