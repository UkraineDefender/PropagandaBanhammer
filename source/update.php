<!DOCTYPE html>
<html lang="ua">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>PropagandaBanhammer - update</title>
        <style>
            html {
                background-color: #21262d;
                color: white;
                font-family: 'Consolas';
            }

            html, body {
                width: 100%;
                height: 100%;
                padding: 0;
                margin: 0;
            }

            .success {
                color: #7EE77A;
            }

            .failed {
                color: #ff7b72;
            }

            .alert {
                color: #ffa657;
            }

            .scrolling-content {
                padding: 50px;
                box-sizing: border-box;
            }

            .dots.waiting {
                display: inline-block;
                background: linear-gradient(to right, rgba(255,255,255,1) 20%, rgba(255,255,255,.1) 40%, rgba(255,255,255,.1) 60%, rgba(255,255,255,1) 80%);
                background-size: 200% auto;
                background-clip: text;
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                animation: shine 0.5s linear infinite reverse;
            }
            
            @keyframes shine {
                to {
                    background-position: 200% center;
                }
            }
        </style>
        <script>
            var scrollingContentLength = 0;

            setInterval(function() {
                var currentScrollingContentLength = document.querySelector(".scrolling-content").outerHTML.length;

                if(currentScrollingContentLength > scrollingContentLength)
                {
                    window.scrollTo(0, document.querySelector(".scrolling-content").scrollHeight);
                    scrollingContentLength = currentScrollingContentLength;
                }

                var outputStringsDots = document.querySelectorAll(".scrolling-content p .dots");

                for(var i = 0; i < outputStringsDots.length - 1; i++)
                {
                    outputStringsDots[i].classList.remove('waiting');
                }

                var lastOutputStringDots = outputStringsDots[outputStringsDots.length - 1];

                lastOutputStringDots.classList.add('waiting');
            }, 300);
        </script>
    </head>
    <body>
        <div class="scrolling-content">
            <?php

            error_reporting(E_ALL ^ E_DEPRECATED);
            set_time_limit(0);
            
            function echoAsync(string $text)
            {
                echo "<p>" . $text . "</p>\n";
                @ob_flush();
                @flush();
            }

            function copyFolder(string $source, string $destination)
            { 
                $dir = opendir($source); 
              
                @mkdir($destination); 
              
                while($file = readdir($dir))
                { 
                    if($file != '.' && $file != '..')
                    { 
                        if(is_dir($source . '/' . $file)) 
                        { 
                            copyFolder($source . '/' . $file, $destination . '/' . $file);
                        } 
                        else
                        { 
                            copy($source . '/' . $file, $destination . '/' . $file); 
                        } 
                    } 
                } 
              
                closedir($dir);
            } 

            echoAsync('Завантажуємо останню версію програми<span class="dots">...</span>');

            $tempDirPath = __DIR__ . '/tmp';
            $zipPath = $tempDirPath . '/latest.zip';

            if(!file_exists($tempDirPath))
                mkdir($tempDirPath);
            
            $downloadedData = @file_get_contents("https://github.com/UkraineDefender/PropagandaBanhammer/archive/master.zip");
            $downloadResult = $downloadedData != false ? @file_put_contents($zipPath, $downloadedData) : false;
            
            if($downloadResult)
            {
                echoAsync('<span class="success">Завантажили!</span><br /><br />');

                echoAsync('Розпаковуємо zip<span class="dots">...</span>');

                $zip = new ZipArchive;
                $zipResource = $zip->open($zipPath);

                if($zipResource === true)
                {
                    $zip->extractTo($tempDirPath);
                    $zip->close();
                    echoAsync('<span class="success">Розпакували!</span><br /><br />');

                    @rename($tempDirPath . '/PropagandaBanhammer-main/source/update.php', $tempDirPath . '/PropagandaBanhammer-main/source/update.php.new');

                    echoAsync('Копіюємо файли<span class="dots">...</span>');
                    copyFolder($tempDirPath . '/PropagandaBanhammer-main/source', __DIR__);
                    
                    echoAsync('<span class="success">Зкопіювали!</span><br /><br />');

                    echoAsync('Видаляємо тимчасові файли<span class="dots">...</span>');

                    $zip->close();
                    
                    $tmpHandle = opendir($tempDirPath);
                    closedir($tmpHandle);
                    @rmdir($tempDirPath);

                    echoAsync('<span class="success">Видалили!</span><br /><br />');

                    echoAsync("<br />\n<br />\nПрограма оновлена успішно.<br />\nЧекайте<span class=\"dots\">...</span>");
                    echoAsync('<script>setTimeout(() => location.replace("/update-finish.php"), 1000);</script>');

                }
                else
                {
                    echoAsync('<span class="failed">Не вдалося оновити програму - помилка розпакування :(</span><br />');
                    exit();
                }
            }
            else
            {
                echoAsync('<span class="failed">Не вдалося оновити програму - помилка завантаження :(</span><br />');
                exit();
            }


            ?>
        </div>
    </body>
</html>