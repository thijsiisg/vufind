#!/bin/bash
#
# iish.archieven.sh
#
# Invoke fop to create PDF files


pdf_folder="/usr/local/vufind/local/cache/pdf"
for file in "${pdf_folder}/*.xml"
do
    pdf="${pdf_folder}/$(basename $file).pdf"
    echo "Creating $pdf from $file"
    /usr/bin/fop -c /usr/local/vufind/local/import/fop/fop.xconf -xml $file -xsl /usr/local/vufind/local/import/fop/xsl/iish.archieven/ead_complete_fo.xsl -pdf $pdf -param path /usr/local/vufind/local/import/fop/xsl -param sysYear $(date +'%Y')
    if [ -f "$pdf" ] ; then
	    chown www-data:www-data "$pdf"
	    chmod 600 "$pdf"
    fi
    rm $file
done
