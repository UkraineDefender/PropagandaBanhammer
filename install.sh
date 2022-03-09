#!/bin/sh

cd ~
if [ -d "uadef" ]; then rm -Rf "uadef"; fi
mkdir -p uadef
cd uadef

curl -L -o tmp.zip https://github.com/UkraineDefender/PropagandaBanhammer/archive/master.zip
echo ''
echo ''
echo ''
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
~/uadef/run.sh