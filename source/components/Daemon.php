<?php

use danog\MadelineProto\stats;

class Daemon
{
    public static function echoAsync(string $text): void
    {
        echo "<p>" . $text . "</p>\n";
        @ob_flush();
        @flush();
    }

    public static function loadCSS(string $Path): void
    {
        if(file_exists($Path))
        {
            $CSSData = @file_get_contents($Path) ?? null;
            if($CSSData != null)
            {
                echo PHP_EOL . '<style>' . PHP_EOL . $CSSData . PHP_EOL . '</style>' . PHP_EOL;
            }
            else
            {
                echo PHP_EOL . '<script>console.error("Resource data from \"' . $Path . '\" is empty. ");</script>' . PHP_EOL;
            }
        }
        else
        {
            echo PHP_EOL . '<script>console.error("Resource \"' . $Path . '\" not found. ");</script>' . PHP_EOL;
        }
    }

    public static function loadJS(string $Path): void
    {
        if(file_exists($Path))
        {
            $CSSData = @file_get_contents($Path) ?? null;
            if($CSSData != null)
            {
                echo PHP_EOL . '<script>' . PHP_EOL . $CSSData . PHP_EOL . '</script>' . PHP_EOL;
            }
            else
            {
                echo PHP_EOL . '<script>console.error("Resource data from \"' . $Path . '\" is empty. ");</script>' . PHP_EOL;
            }
        }
        else
        {
            echo PHP_EOL . '<script>console.error("Resource \"' . $Path . '\" not found. ");</script>' . PHP_EOL;
        }
    }

    public static function mtime()
    {
        return round(microtime(true) * 1000);
    }
    
    public static function sleep(int $seconds, ?callable $tickAction = null): void
    {
        $startTime = self::mtime();
        $endTime = $startTime + ($seconds * 1000);
    
        $prevS = time();
        $loopCanRun = self::mtime() < $endTime;
    
        while($loopCanRun)
        {
            $currentS = time();
            if($currentS != $prevS)
            {
                if(is_callable($tickAction))
                {
                    call_user_func($tickAction);
                }

                $prevS = $currentS;
            }
    
            usleep(50000);
    
            $loopCanRun = self::mtime() < $endTime;
        }
    }

    public static function uiWait(int $waitTime, BanhammerAnalytics $analytics, \danog\MadelineProto\API $MadelineProto): void
    {
        $currentTimestamp = time();
        $waitEndTimestamp = time() + $waitTime;

        self::echoAsync("Для безпеки чекаємо <span class=\"wait-time\" data-start=\"$currentTimestamp\" data-end=\"$waitEndTimestamp\">$waitTime</span> сек.<span class=\"dots\">...</span>");
        self::sleep($waitTime, function() use($analytics, $MadelineProto) {
            $analytics->updateLastOnline();

            if(connection_status() != CONNECTION_NORMAL)
            {
                $MadelineProto->stop();
                exit();
            }
        });
    }
}