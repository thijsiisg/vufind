#!/bin/bash
#
# fop-iish.archieven.sh
#
# Make the call to create PDF files


pdf_folder="/usr/local/vufind/local/cache/pdf"
cd $pdf_folder
for file in *.xml
do
    pdf="{$pdf_folder}/$(basename $file).pdf"
    echo "Creating $pdf from $file"
    fop -c /usr/local/vufind/local/import/fop/fop.xconf -xml $file -xsl /usr/local/vufind/local/import/xsl/ead_complete_fo.xsl -pdf $pdf -param path /usr/local/vufind/local/import/xsl -param sysYear $(date +'%Y')
    if [ -f "$pdf" ] ; then
	    chown www-data "$pdf"
    fi
    rm $file
done
