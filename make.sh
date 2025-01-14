#!/bin/bash
#
# make.sh
#
# Build a package

set -e

instance=$1
if [ -z "$instance" ] ; then
    instance="vufind"
	echo "Default version ${vufind}"
fi

version=$2
if [ -z "$version" ] ; then
	version="2.3.1"
	echo "Default version ${version}"
fi

if [ -d target ] ; then
    rm -rf target
fi
mkdir target


revision=$(git rev-parse HEAD)
app=$instance-$version
expect=target/$app.tar.gz

echo "Build $expect, revision $revision"

app=$instance-$version
if [ -d $app ] ; then
    rm -rf $app
fi


rsync -av --exclude='.git' --exclude='.gitignore' --exclude='make.sh' . $app
# set permissions
for f in $(find "$app" -type f  -name "*.sh" )
do
    chmod 744 $f
done

echo $revision>$app/revision.txt

composer --working-dir=$app install
cd $app
npm install --production
mv ./node_modules/ ./themes/iish/vendor/
php ./util/cssBuilder.php iish
cd ..

tar -zcvf $expect $app
rm -rf $app

if [ -f $expect ] ; then
    echo "Build ok."
    exit 0
else
    echo -e "Unable to build ${expect}"
fi
