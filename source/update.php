<?php
    require_once 'components/Daemon.php';
?>
<!DOCTYPE html>
<html lang="ua">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>PropagandaBanhammer - update</title>

        <?php Daemon::loadCSS(__DIR__ . '/web/assets/css/main.css'); ?>
        <?php Daemon::loadJS(__DIR__ . '/web/assets/js/main.js'); ?>
    </head>
    <body>
        <div class="scrolling-content">
            <?php

            error_reporting(E_ALL ^ E_DEPRECATED);
            set_time_limit(0);

            Daemon::echoAsync('Завантажуємо останню версію програми<span class="dots">...</span>');

            $tempDirPath = __DIR__ . '/tmp';
            $zipPath = $tempDirPath . '/latest.zip';

            if(!file_exists($tempDirPath))
                mkdir($tempDirPath);
            
            $downloadedData = @file_get_contents("https://github.com/UkraineDefender/PropagandaBanhammer/archive/master.zip");
            $downloadResult = $downloadedData != false ? @file_put_contents($zipPath, $downloadedData) : false;
            
            if($downloadResult)
            {
                Daemon::echoAsync('<span class="success">Завантажили!</span><br /><br />');

                Daemon::echoAsync('Розпаковуємо zip<span class="dots">...</span>');

                $zip = new ZipArchive;
                $zipResource = $zip->open($zipPath);

                if($zipResource === true)
                {
                    $zip->extractTo($tempDirPath);
                    $zip->close();
                    Daemon::echoAsync('<span class="success">Розпакували!</span><br /><br />');

                    @rename($tempDirPath . '/PropagandaBanhammer-main/source/update.php', $tempDirPath . '/PropagandaBanhammer-main/source/update.php.new');

                    Daemon::echoAsync('Копіюємо файли<span class="dots">...</span>');
                    Daemon::copyFolder($tempDirPath . '/PropagandaBanhammer-main/source', __DIR__);
                    
                    Daemon::echoAsync('<span class="success">Зкопіювали!</span><br /><br />');
                    Daemon::echoAsync('Видаляємо тимчасові файли<span class="dots">...</span>');

                    Daemon::removeDir($tempDirPath);

                    Daemon::echoAsync('<span class="success">Видалили!</span><br /><br />');
                    Daemon::echoAsync("<br />\n<br />\nПрограма оновлена успішно.<br />\nЧекайте<span class=\"dots\">...</span>");
                    Daemon::echoAsync('<script>setTimeout(() => location.replace("/update-finish.php"), 1000);</script>');

                }
                else
                {
                    Daemon::echoAsync('<span class="failed">Не вдалося оновити програму - помилка розпакування :(</span><br />');
                    exit();
                }
            }
            else
            {
                Daemon::echoAsync('<span class="failed">Не вдалося оновити програму - помилка завантаження :(</span><br />');
                exit();
            }

            ?>
        </div>
    </body>
</html>