#!/bin/bash
set -x

DSC=/usr/lib/dirsrv/dscontainer

$DSC -r &
DSC_PID=$!

sleep 15

counter=0
until $DSC -H
do
   sleep 1
   ((counter++))
done

sleep 5

dsconf localhost backend create --suffix "dc=example,dc=test" --be-name userRoot --create-suffix
dsconf localhost backend import userRoot /asd.ldif

kill -15 $DSC_PID

$DSC -r