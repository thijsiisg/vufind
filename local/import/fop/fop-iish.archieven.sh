#!/bin/sh
# Load all of the .jar files in the lib directory into the classpath

source /usr/local/vufind/local/import/fop/config.sh

folder=$SHARE/cache/xml
target=$SHARE/cache/pdf

for file in ${folder}/* ; do
    pdf=$target/$(basename $file .xml).pdf
    echo "Creating $pdf from $file"
    fop -c $VUFIND_LOCAL_DIR/import/fop/fop.xconf -xml $file -xsl $VUFIND_LOCAL_DIR/import/xsl/ead_complete_fo.xsl -pdf $pdf -param path $VUFIND_LOCAL_DIR/import/xsl -param sysYear $(date +'%Y')
    if [ -f "$pdf" ] ; then
	    chown www-data "$pdf"
    fi
done
