@echo off

cd source

if exist ..\php\php.exe (
    echo 1 >NUL
) else (
    if exist ..\php\tmp.zip (
        echo 1 >NUL
    ) else (
        ..\tools\wget.exe -O ..\php\tmp.zip https://windows.php.net/downloads/releases/php-8.1.3-Win32-vs16-x64.zip
    )

    if exist ..\php\tmp.zip (
        ..\tools\unzip.exe ..\php\tmp.zip -d ..\php\
        copy ..\tools\php\php.ini ..\php\php.ini
        del ..\php\tmp.zip
    ) else (
        echo PHP download failed.
    )
)

..\php\php.exe -S localhost:3539
