#!/bin/sh

cd ~
if [ -d "uadef" ]; then rm -Rf "uadef"; fi
mkdir -p uadef
cd uadef

curl -L -o tmp.zip https://github.com/UkraineDefender/PropagandaBanhammer/archive/master.zip
echo ''
echo ''
echo ''
if ! command -v unzip &> /dev/null
then
    case "$(uname -s)" in
    Linux)
        sudo apt-get install unzip
    ;;

    *)
        echo 'Unzip is missing on your system. You need to install it manually and then try again.'
        exit
    ;;
esac
fi
unzip tmp.zip
echo ''
echo ''
echo ''
rm tmp.zip
echo ''
echo ''
echo ''
mv -v ~/uadef/PropagandaBanhammer-main/* ~/uadef/
mv -v ~/uadef/PropagandaBanhammer-main/.gitignore ~/uadef/
echo ''
echo ''
echo ''
if [ -d "PropagandaBanhammer-main" ]; then rm -Rf "PropagandaBanhammer-main"; fi
echo ''
echo ''
echo ''
cd ~/uadef
bash ~/uadef/run.sh