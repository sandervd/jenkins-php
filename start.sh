#!/bin/bash
docker run -p 8080:8080 -u root -v ./jenkins:/var/jenkins_home jenkins
