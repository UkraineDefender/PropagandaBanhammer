<!DOCTYPE html>
<html lang="ua">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PropagandaBanhammer</title>
</head>
<body>
    <style>
        html {
            background-color: #21262d;
            color: white;
            font-family: 'Consolas';
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
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            setTimeout(function() {
                window.location.reload();
            }, 3000);
        });
        setInterval(function() {
            window.scrollTo(0, document.querySelector(".scrolling-content").scrollHeight);
        }, 300);
    </script>
        <div class="scrolling-content">
            <?php

            error_reporting(E_ALL ^ E_DEPRECATED);
            set_time_limit(0);

            require_once 'vendor/autoload.php';

            function echoAsync($text)
            {
                echo $text . "<br />\n";
                @flush();
                @ob_flush();
            }

            echoAsync('Завантажуємо конфігурацію...');

            $configPath = __DIR__ . '/../config.json';
            $configContent = @file_get_contents($configPath) ?? null;
            $config = @json_decode($configContent) ?? null;

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
                echoAsync('Входимо у Telegram аккаунт...');

                $settings = new \danog\MadelineProto\Settings;
                $appInfo = new \danog\MadelineProto\Settings\AppInfo;

                $appInfo->setApiId('16235650');
                $appInfo->setApiHash('0dd283cde9a1696ee945876115ce8eca');

                $settings->setAppInfo($appInfo);

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
                        echoAsync("<br />\nПробуємо відіслати репорт на " . $peerToReport);
                        
                        try
                        {
                            $reportResult = $MadelineProto->account->reportPeer(['peer' => $peerToReport, 'reason' => $reportReasons[array_rand($reportReasons)], 'message' => $reportReasonsText[array_rand($reportReasonsText)]]);
                            echoAsync($reportResult ? '<span class="success">Вийшло!</span>' : '<span class="failed">Не вийшло :(</span>');
                        }
                        catch (Exception $e)
                        {
                            $errorMessage = $e->getMessage();

                            if(!str_contains($errorMessage, 'FLOOD_WAIT_'))
                            {
                                echoAsync('<span class="alert">Помилка: ' . $e->getMessage() . '</span>');
                            }
                            else
                            {
                                echoAsync('<span class="alert">Telegram тимчасово не дозволяє відправляти репорти з аккаунту: ' . $e->getMessage() . '</span>');
                                $MadelineProto->stop();
                                break;
                            }

                            $MadelineProto->logger($e);
                        }

                        $waitTime = rand(4, 10);
                        echoAsync("<br />\nДля безпеки чекаємо $waitTime секунд.");
                        sleep($waitTime);

                    }
                }
                else
                {
                    echoAsync('Програма може працювати тільки з використянням звичайного акаунту.');
                }

                echoAsync('<br />\n<br />\nПрограма виконана успішно!');
            }
            else
            {
                echoAsync("Конфігураційний файл не знайдений або невірний. Запуск неможливий.");
            }
            ?>
        </div>
    </body>
</html>