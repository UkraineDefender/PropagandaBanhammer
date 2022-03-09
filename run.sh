#!/bin/sh

if ! [ -d "source" ] 
then
    echo ''
    echo '--------------------------'
    echo -e '\e[31mFatal error: project corrupted'
    exit
fi

cd ~/uadef/source

case "$(uname -s)" in

    Darwin)
        if ! command -v php &> /dev/null
        then
            if ! command -v brew &> /dev/null
            then
                echo ''
                echo 'The latest version of brew is being installed...'
                echo '-----------------------------------------------------------'
                echo ''
                /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

                echo ''
                echo 'Adding brew to PATH...'
                echo '-----------------------------------------------------------'
                echo ''

                eval "$(/opt/homebrew/bin/brew shellenv)"
            fi 

            if ! command -v brew &> /dev/null
            then
                echo ''
                echo ''
                echo ''
                echo '--------------------------'
                echo -e '\e[31mFatal installation error: brew was not installed'
                exit
            fi

            echo ''
            echo 'Installing the latest version of PHP...'
            echo '-----------------------------------------------------------'
            echo ''

            brew install php@8.0

            echo ''
            echo 'Adding PHP to PATH...'
            echo '-----------------------------------------------------------'
            echo ''
            export PATH="/usr/local/opt/php@8.0/bin:$PATH"

            if ! command -v php &> /dev/null
            then
                echo ''
                echo ''
                echo ''
                echo '--------------------------'
                echo -e '\e[31mFatal installation error: php was not installed'
                exit
            fi
        fi

        php -S localhost:3539
    ;;

    Linux)
        if ! command -v php &> /dev/null
        then
            if ! command -v brew &> /dev/null
            then
                echo ''
                echo 'The latest version of brew is being installed...'
                echo '-----------------------------------------------------------'
                echo ''
                /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

                echo ''
                echo 'Adding brew to PATH...'
                echo '-----------------------------------------------------------'
                echo ''

                eval "$(/home/linuxbrew/.linuxbrew/bin/brew shellenv)"
            fi 

            if ! command -v brew &> /dev/null
            then
                echo ''
                echo ''
                echo ''
                echo '--------------------------'
                echo -e '\e[31mFatal installation error: brew was not installed'
                exit
            fi

            echo ''
            echo 'Installing the latest version of PHP...'
            echo '-----------------------------------------------------------'
            echo ''

            brew install php@8.0

            echo ''
            echo 'Adding PHP to PATH...'
            echo '-----------------------------------------------------------'
            echo ''
            export PATH="/home/linuxbrew/.linuxbrew/opt/php@8.0/bin:$PATH"
            export PATH="/home/linuxbrew/.linuxbrew/opt/php@8.0/sbin:$PATH"

            if ! command -v php &> /dev/null
            then
                echo ''
                echo ''
                echo ''
                echo '--------------------------'
                echo -e '\e[31mFatal installation error: php was not installed'
                exit
            fi
        fi

        php -S localhost:3539
    ;;

    CYGWIN*|MINGW32*|MSYS*|MINGW*)
        echo -e '\e[33mPlease open run.bat instead of this file'
        exit
    ;;

    *)
        echo 'Your OS is not supported'
        exit
    ;;
esac