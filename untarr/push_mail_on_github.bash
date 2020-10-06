#!/bin/bash

git clone http://github.com/nsgw/emails.git  /home/pi/push_email
if [ $? -ne 0 ]
then
    echo "ERROR FAILED TO CLONE EMAILS REPO"
    exit 1
fi

cd /home/pi/push_email

cp /home/pi/update-gateway/dot_gitconfig  /home/pi/.gitconfig
if [ $? -ne 0 ]
then
    echo "ERROR FAILED TO COPY DOT GITCONFIG"
    exit 1
fi

chown --verbose  pi:pi  /home/pi/.gitconfig
if [ $? -ne 0 ]
then
    echo "ERROR FAILED TO CHOWN DOT GITCONFIG"
    exit 1
fi


cp /home/pi/update-gateway/dot_git-credentials  /home/pi/.git-credentials
if [ $? -ne 0 ]
then
    echo "ERROR FAILED TO COPY DOT GIT-CREDENTIALS"
    exit 1
fi

chown --verbose  pi:pi  /home/pi/.git-credentials
if [ $? -ne 0 ]
then
    echo "ERROR FAILED TO CHOWN DOT GIT-CREDENTIALS"
    exit 1
fi

git config credential.helper  store
if [ $? -ne 0 ]
then
    echo "ERROR"
    exit 1
fi

git push https://github.com/nsgw/emails.git
if [ $? -ne 0 ]
then
    echo "ERROR FAILED TO PUSH TO REPO EMAILS"
    exit
fi

cp /home/pi/mailee  $(hostname)
if [ $? -ne 0 ]
then
    echo "ERROR FAILED TO COPY MAILEE"
    exit
fi

git add mailee
if [ $? -ne 0 ]
then
    echo "ERROR FAILED TO GIT ADD MAILEE"
    exit
fi

git commit -m "mailee"
if [ $? -ne 0 ]
then
    echo "ERROR FAILED TO GIT COMMIT MAILEE"
    exit
fi

git push origin main
if [ $? -ne 0 ]
then
    echo "ERROR FAILED TO GIT PUSH ORIGIN MAIN (file mailee)"
    exit
fi

rm -rf /home/pi/push_email

