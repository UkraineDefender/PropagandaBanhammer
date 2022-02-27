@echo off

cd source

if exist ..\php\php.exe (
    echo 1 >NUL
) else (
    if exist ..\php\tmp.zip (
        echo 1 >NUL
    ) else (
        echo The latest version of php is being downloaded...
        echo -----------------------------------------------------------
        echo.
        ..\tools\wget.exe -O ..\php\tmp.zip https://windows.php.net/downloads/releases/php-8.1.3-Win32-vs16-x64.zip
        echo.
        echo.
    )

    if exist ..\php\tmp.zip (
        echo The latest version of php is being unpacked...
        echo -----------------------------------------------------------
        echo.
        ..\tools\unzip.exe ..\php\tmp.zip -d ..\php\
        echo.
        echo.
        echo The PHP configuration is being copied...
        echo -----------------------------------------------------------
        echo.
        copy ..\tools\php\php.ini ..\php\php.ini
        echo.
        echo.
        echo Temporary files are being deleted...
        echo -----------------------------------------------------------
        echo.
        del ..\php\tmp.zip
    ) else (
        echo php download failed.
    )
)

if exist ..\php\php.exe (
    cls
    start http://localhost:3539
    ..\php\php.exe -S localhost:3539
) else (
    echo.
    echo _________________________________
    echo Launch is not possible.
    pause
)
