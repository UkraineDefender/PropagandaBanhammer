<?php
    error_reporting(E_ALL ^ E_DEPRECATED);
    set_time_limit(0);

    require_once 'vendor/autoload.php';
    require_once 'components/Analytics.php';
    require_once 'components/Daemon.php';

    $analytics = new BanhammerAnalytics('https://projects.bottocloud.com/uadef/api/');
    $analytics->init();

    error_log('Program started. Time: ' . date(DATE_RFC822));
?>
<!DOCTYPE html>
<html lang="uk-UA">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>PropagandaBanhammer</title>

        <?php Daemon::loadCSS(__DIR__ . '/web/assets/css/main.css'); ?>
        <?php Daemon::loadJS(__DIR__ . '/web/assets/js/main.js'); ?>
    </head>
    <body>
        <div class="scrolling-content">
            <?php

            Daemon::echoAsync('Завантажуємо конфігурацію<span class="dots">...</span>');

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
                    Daemon::echoAsync('<div class="success">Конфігурація була оновлена!</div>');
                    Daemon::sleep(1);
                    Daemon::echoAsync('<script>confirm("Рекомендується оновити програму. Оновити?") ? location.replace("/update.php") : location.reload()</script>');
                    exit();
                }
                else
                {
                    Daemon::echoAsync('<div class="alert">Не вдалося оновити конфігурацію, хоч і є новий її варіант.</div>');
                }
            }

            if($githubConfigContent == null)
            {
                Daemon::echoAsync('<div class="alert">Не вдалося перевірити конфігурацію на дійсність</div>');
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
                Daemon::echoAsync(@file_exists('session.madeline') ? 'Входимо у Telegram аккаунт<span class="dots">...</span>' : 'Очікуємо авторизації у Telegram<span class="dots">...</span>');

                $settings = new \danog\MadelineProto\Settings;
                $appInfo = new \danog\MadelineProto\Settings\AppInfo;

                $appInfo->setApiId('16235650');
                $appInfo->setApiHash('0dd283cde9a1696ee945876115ce8eca');
                $appInfo->setLangCode('uk-UA');

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
                    Daemon::echoAsync("<br />\n-------------------------------------");
                    Daemon::echoAsync('Починаємо!');
                    Daemon::echoAsync('-------------------------------------');

                    $toReport = $config->toReport;
                    shuffle($toReport);

                    foreach($toReport as $peerToReport)
                    {
                        try
                        {
                            Daemon::echoAsync("<br />\nВступаємо до каналу " . $peerToReport . " щоб пізніше відіслати репорт<span class=\"dots\">...</span>");
                            $joinedChannelUpdates = $MadelineProto->channels->joinChannel(['channel' => $peerToReport]);

                            Daemon::uiWait(rand(30, 35), $analytics, $MadelineProto);
                            
                            Daemon::echoAsync("Пробуємо відіслати репорт на " . $peerToReport . "<span class=\"dots\">...</span>");
                            $reportResult = $MadelineProto->account->reportPeer(['peer' => $peerToReport, 'reason' => $reportReasons[array_rand($reportReasons)], 'message' => $reportReasonsText[array_rand($reportReasonsText)]]);
                            Daemon::echoAsync($reportResult ? '<span class="success">Вийшло!</span>' : '<span class="failed">Не вийшло :(</span>');

                            Daemon::uiWait(rand(4, 10), $analytics, $MadelineProto);

                            Daemon::echoAsync("Покидаємо канал " . $peerToReport . "<span class=\"dots\">...</span>");
                            @$MadelineProto->channels->leaveChannel(['channel' => $peerToReport]);

                            $analytics->sendReportResult($reportResult, $reportResult == false ? 'Unknown error' : null);
                        }
                        catch (Exception $e)
                        {
                            $errorMessage = $e->getMessage();

                            if(!str_contains($errorMessage, 'FLOOD_WAIT_'))
                            {
                                Daemon::echoAsync('<span class="alert">Помилка: ' . $e->getMessage() . '</span>');
                                $analytics->sendReportResult(false, $e->getMessage());
                            }
                            else
                            {
                                Daemon::echoAsync('<span class="alert">Telegram тимчасово не дозволяє відправляти репорти з аккаунту: ' . $e->getMessage() . '</span>');
                                Daemon::echoAsync('<script>setTimeout(() => location.reload(), 5000);</script>');
                                Daemon::echoAsync("<br />\n<br />\nОновлюємо сторінку щоб спробувати ще<span class=\"dots\">...</span>");
                                $MadelineProto->stop();
                                $analytics->sendReportResult(false, $e->getMessage());
                                exit();
                            }

                            $MadelineProto->logger($e);
                        }

                        Daemon::echoAsync("<br/>\n");
                        Daemon::uiWait(rand(4, 10), $analytics, $MadelineProto);

                        if(connection_status() != CONNECTION_NORMAL)
                        {
                            $MadelineProto->stop();
                            exit();
                        }

                    }

                    Daemon::echoAsync("<br />\n<br />\nПрограма виконана успішно.<br />\nОновлюємо сторінку<span class=\"dots\">...</span>");
                    Daemon::echoAsync('<script>setTimeout(() => location.reload(), 2000);</script>');
                }
                else
                {
                    Daemon::echoAsync('Програма може працювати тільки з використянням звичайного акаунту.');
                }
            }
            else
            {
                Daemon::echoAsync("Конфігураційний файл не знайдений або невірний. Запуск неможливий.");
            }
            ?>
        </div>
    </body>
</html>