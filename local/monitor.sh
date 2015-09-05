#!/bin/bash
#
# monitor.sh [status file]
#
# Purpose: place a status file in the root of the web application if all is well.
# If not: replace the file and try to restart the applications.


if (( $(pgrep -c "monitor.sh") == 1 )) ; then
    echo "Self"
else
    echo "Already running"
    exit 1
fi


f=$1
if [ -z "$f" ] ; then
    echo "Warning. No file parameter found. Usage: ./monitor.sh /path/to/root/of/the/webapplication/status.txt"
    exit 1
fi

body=/tmp/body.txt
content=/tmp/content.txt
headers=/tmp/headers.txt
s=/opt/status.txt
q="http://localhost:8080/solr/biblio/select"

rm -f $content $headers $body
wget -S -T 5 -t 3 -O $content $q 2>$headers
rc=$?
if [[ $rc == 0 ]] ; then
    echo "$(date)">$f
    exit 0
else
    rm -f $f
    echo "$(date): Invalid response ${rc}" >> $s

    # Headers
    if [ ! -f $headers ]
    then
        echo "There is no headers file." > $headers
    fi

    # Content
    if [ ! -f $content ]
    then
        echo "There is no content file." > $O
    fi

    echo "Headers:
    $headers
    " >> "$body"

    echo "Content:
    $content
    " >> "$body"

    echo "Top:
    $(top -b -n 1)
    " >> "$body"

    echo "Restart event history:
    $s
    " >> "$body"

    service vufind stop
    sleep 7
    killall java
    sleep 7
    service vufind start
    sleep 30

    subject="${HOSTNAME} - Automatic restart by ${0}"
    /usr/bin/sendmail --body "$body" --from "search@${HOSTNAME}" --to "$MAIL_TO" --subject "$subject" --mail_relay "$VUFIND_MAIL_HOST"

    exit 1
fi
