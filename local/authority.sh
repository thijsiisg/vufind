#!/bin/bash
#
# authority.sh
#
# Purpose: because of https://jira.socialhistoryservices.org/browse/SEARCHM-66
#          we want to make sure the index is there. If not, remove the index and start again.
#          Schedule this task to execute some time after the index job on the master has finished.


if (( $(pgrep -c "authority.sh") == 1 )) ; then
    echo "Self"
else
    echo "Already running"
    exit 1
fi


if [ -d "/data/solr/${HOSTNAME}/authority/index" ] ; then
    exit 0
else
    rm -rf "/data/solr/${HOSTNAME}/authority/"*
    service vufind restart
    body=/dev/null
    subject="${HOSTNAME} - Could not find an authority index. Automatic restart vufind by ${HOSTNAME} was required"
    /usr/bin/sendmail --body "$body" --from "search@${HOSTNAME}" --to "$MAIL_TO" --subject "$subject" --mail_relay "$VUFIND_MAIL_HOST"
    exit 1
fi