#!/bin/bash
#
# Syncs the PDF documents and authority databases.

for slave in $VUFIND_SLAVES
do
    host=$slave.iisg.net
    sudo -u vufind rsync -av --progress "${VUFIND_SHARE}/solr/${HOSTNAME}/alphabetical_browse" "${host}:/${VUFIND_SHARE}/solr/${slave}/"
    sudo -u vufind rsync -av --progress "${VUFIND_CACHE_CACHE_DIR}/pdf/"*.pdf "${host}:/${VUFIND_CACHE_CACHE_DIR}/pdf"
done
