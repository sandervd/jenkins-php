#!/bin/bash
if [ -e .docker-id ]
then
	echo "Container is already running."
	exit 1
fi
#PID=`docker run -d -p 8080:8080 -u root -v /home/tinne/playground/jenkins:/var/jenkins_home jenkins`
PID = `docker run -p 8080:8080 -u root jenkins`
echo $PID > .docker-id
echo "backgrounded"
docker attach $PID
echo "hello"
rm .docker-id
