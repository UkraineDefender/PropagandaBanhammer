<?php

if(file_exists(__DIR__ . '/update.php.new'))
{
    unlink(__DIR__ . '/update.php');
    rename(__DIR__ . '/update.php.new', __DIR__ . '/update.php');
    header('Location: /');
}
else
{
    echo 'Error. New file not exist';
}