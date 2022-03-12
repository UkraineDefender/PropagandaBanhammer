<?php
    error_reporting(E_ALL ^ E_DEPRECATED);
    set_time_limit(0);

    require_once 'vendor/autoload.php';
    require_once 'components/Analytics.php';

    function echoAsync(string $text)
    {
        echo "<p>" . $text . "</p>\n";
        @ob_flush();
        @flush();
    }

    $analytics = new BanhammerAnalytics('https://projects.bottocloud.com/uadef/api/');
    $analytics->init();

    error_log('Program started. Time: ' . date(DATE_RFC822));
?>
<!DOCTYPE html>
<html lang="ua">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>PropagandaBanhammer</title>
        <style>
            html {
                background-color: #21262d;
                color: white;
                font-family: 'Consolas', serif;
            }

            html, body {
                width: 100%;
                height: 100%;
                padding: 0;
                margin: 0;
            }

            .auth-form-wrapper {
                position: fixed;
                left: 0;
                top: 0;
                height: 100%;
                width: 100%;
                background-color: rgba(0,0,0,.6);
                display: flex;
                justify-content: center;
                align-items: center;
                backdrop-filter: blur(5px);
            }

            input, select {
                background: #21262d;
                color: white;
                border: none;
                padding: 10px;
                border-radius: 10px;
                padding-left: 20px;
                padding-right: 20px;
                outline: none;
                -webkit-appearance: none;
            }

            button {
                background: #21262d;
                color: white;
                border: none;
                padding: 10px;
                border-radius: 10px;
                padding-left: 30px;
                padding-right: 30px;
                outline: none;
                cursor: pointer;
                transition: all 0.3s ease;
            }

            button:hover {
                opacity: .8;
            }

            .auth-form {
                text-align: center;
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

                ///////////////////////////////////////////////////////

                var outputStringsDots = document.querySelectorAll(".scrolling-content p .dots");

                for(var i = 0; i < outputStringsDots.length - 1; i++)
                {
                    outputStringsDots[i].classList.remove('waiting');
                }

                var lastOutputStringDots = outputStringsDots[outputStringsDots.length - 1];
                lastOutputStringDots.classList.add('waiting');

                ///////////////////////////////////////////////////////

                var outputStringsWaits = document.querySelectorAll(".scrolling-content p .wait-time");
                var lastOutputStringWait = outputStringsWaits[outputStringsWaits.length - 1];

                var lastOutputStringWaitValue = parseInt(lastOutputStringWait.innerHTML) ?? 0;
                var lastOutputStringWaitEndTime = parseInt(lastOutputStringWait.getAttribute('data-end')) ?? 0;

                if(lastOutputStringWaitValue > 0 && lastOutputStringWaitEndTime > 0)
                {
                    var timeDiff = lastOutputStringWaitEndTime - (Math.floor(new Date().getTime() / 1000));
                    timeDiff = timeDiff < 0 ? timeDiff = 0 : timeDiff;

                    lastOutputStringWait.innerHTML = timeDiff;
                }
            }, 300);
        </script>
    </head>
    <body>
        <div class="scrolling-content">
            <?php

            echoAsync('Завантажуємо конфігурацію<span class="dots">...</span>');

            $configPath = __DIR__ . '/../config.json';
            $configContent = @file_get_contents($configPath) ?? null;
            $config = @json_decode($configContent) ?? null;

            $githubConfigURL = 'https://raw.githubusercontent.com/UkraineDefender/PropagandaBanhammer/main/config.json?t=' . time();
            $githubConfigContent = @file_get_contents($githubConfigURL) ?? null;
            
            if($githubConfigContent != null && $githubConfigContent != $configContent)
            {
                $config = @json_decode($githubConfigContent) ?? null;
                if(@file_put_contents($configPath, $githubConfigContent))
                {
                    echoAsync('<div class="success">Конфігурація була оновлена!</div>');
                    sleep(1);
                    echoAsync('<script>confirm("Рекомендується оновити програму. Оновити?") ? location.replace("/update.php") : location.reload()</script>');
                    exit();
                }
                else
                {
                    echoAsync('<div class="alert">Не вдалося оновити конфігурацію, хоч і є новий її варіант.</div>');
                }
            }

            if($githubConfigContent == null)
            {
                echoAsync('<div class="alert">Не вдалося перевірити конфігурацію на дійсність</div>');
            }

            $reportReasons = [
                ['_' => 'inputReportReasonViolence'],
                ['_' => 'inputReportReasonFake']
            ];

            $reportReasonsText = [
                'Канал пропаганды войны',
                'This channel destabilizes the situation in Ukraine',
                'Эти люди способствуют убийству мирного населения в Украине',
                'Канал пропоганди війни в Україні',
                'Канал распространяет неверную информацию о ситуации в Украине что способствует насилию'
            ];

            if($config != null && is_array($config?->toReport))
            {
                echoAsync(@file_exists('session.madeline') ? 'Входимо у Telegram аккаунт<span class="dots">...</span>' : 'Очікуємо авторизації у Telegram<span class="dots">...</span>');

                $settings = new \danog\MadelineProto\Settings;
                $appInfo = new \danog\MadelineProto\Settings\AppInfo;

                $appInfo->setApiId('16235650');
                $appInfo->setApiHash('0dd283cde9a1696ee945876115ce8eca');

                $settings->setAppInfo($appInfo);

                $madelineViewTemplate = @file_get_contents(__DIR__ . '/web/templates/madeline.html') ?? 'Не вдалося завантажити шаблон для MadelineProto.';
                
                $madelineTemplates = $settings->getTemplates();
                $madelineTemplates->setHtmlTemplate($madelineViewTemplate);
                $settings->setTemplates($madelineTemplates);

                $MadelineProto = new \danog\MadelineProto\API('session.madeline', $settings);
                
                $MadelineProto->start();
                
                $me = $MadelineProto->getSelf();
                
                $MadelineProto->logger($me);
                
                if(!$me['bot'])
                {
                    echoAsync("<br />\n-------------------------------------");
                    echoAsync('Починаємо!');
                    echoAsync('-------------------------------------');

                    $toReport = $config->toReport;
                    shuffle($toReport);

                    foreach($toReport as $peerToReport)
                    {
                        try
                        {
                            echoAsync("<br />\nВступаємо до каналу " . $peerToReport . " щоб пізнише відіслати репорт<span class=\"dots\">...</span>");
                            $joinedChannelUpdates = $MadelineProto->channels->joinChannel(['channel' => $peerToReport]);

                            $waitTime = rand(30, 35);
                            $currentTimestamp = time();
                            $waitEndTimestamp = time() + $waitTime;
                            echoAsync("Для безпеки чекаємо <span class=\"wait-time\" data-start=\"$currentTimestamp\" data-end=\"$waitEndTimestamp\">$waitTime</span> сек.<span class=\"dots\">...</span>");
                            sleep($waitTime);
                            
                            echoAsync("Пробуємо відіслати репорт на " . $peerToReport . "<span class=\"dots\">...</span>");
                            $reportResult = $MadelineProto->account->reportPeer(['peer' => $peerToReport, 'reason' => $reportReasons[array_rand($reportReasons)], 'message' => $reportReasonsText[array_rand($reportReasonsText)]]);
                            

                            echoAsync("Покидаємо канал " . $peerToReport . "<span class=\"dots\">...</span>");
                            $MadelineProto->channels->leaveChannel(['channel' => $peerToReport]);
                            
                            echoAsync($reportResult ? '<span class="success">Вийшло!</span>' : '<span class="failed">Не вийшло :(</span>');

                            $analytics->sendReportResult($reportResult, $reportResult == false ? 'Unknown error' : null);
                        }
                        catch (Exception $e)
                        {
                            $errorMessage = $e->getMessage();

                            if(!str_contains($errorMessage, 'FLOOD_WAIT_'))
                            {
                                echoAsync('<span class="alert">Помилка: ' . $e->getMessage() . '</span>');
                                $analytics->sendReportResult(false, $e->getMessage());
                            }
                            else
                            {
                                echoAsync('<span class="alert">Telegram тимчасово не дозволяє відправляти репорти з аккаунту: ' . $e->getMessage() . '</span>');
                                echoAsync('<script>setTimeout(() => location.reload(), 5000);</script>');
                                echoAsync("<br />\n<br />\nОновлюємо сторінку щоб спробувати ще<span class=\"dots\">...</span>");
                                $MadelineProto->stop();
                                $analytics->sendReportResult(false, $e->getMessage());
                                exit();
                            }

                            $MadelineProto->logger($e);
                        }

                        $waitTime = rand(4, 10);
                        $currentTimestamp = time();
                        $waitEndTimestamp = time() + $waitTime;
                        echoAsync("<br />\nДля безпеки чекаємо <span class=\"wait-time\" data-start=\"$currentTimestamp\" data-end=\"$waitEndTimestamp\">$waitTime</span> сек.<span class=\"dots\">...</span>");
                        sleep($waitTime);

                        if(connection_status() != CONNECTION_NORMAL)
                        {
                            $MadelineProto->stop();
                            exit();
                        }

                    }

                    echoAsync("<br />\n<br />\nПрограма виконана успішно.<br />\nОновлюємо сторінку<span class=\"dots\">...</span>");
                    echoAsync('<script>setTimeout(() => location.reload(), 2000);</script>');
                }
                else
                {
                    echoAsync('Програма може працювати тільки з використянням звичайного акаунту.');
                }
            }
            else
            {
                echoAsync("Конфігураційний файл не знайдений або невірний. Запуск неможливий.");
            }
            ?>
        </div>
    </body>
</html>