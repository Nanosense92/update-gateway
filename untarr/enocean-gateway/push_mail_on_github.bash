#!/bin/bash

sudo rm -rf /home/pi/push_email  
sudo rm -rf /home/pi/.gitconfig  /home/pi/.git-credentials # legacy for old files not used anymore


sleep $(( $RANDOM % 7200 + 1 ))


ssh-keyscan github.com >> ~/.ssh/known_hosts

# git clone
git clone git@github.com:nsgw/emails.git   /home/pi/push_email
if [ $? -ne 0 ]
then
    echo "ERROR FAILED TO CLONE EMAILS REPO"
    exit 1
fi

cd /home/pi/push_email

# create file to push
cp /home/pi/mailee  $(hostname)
if [ $? -ne 0 ]
then
    echo "ERROR FAILED TO COPY MAILEE"
    exit 1
fi

# git add file
git add $(hostname)
if [ $? -ne 0 ]
then
    echo "ERROR FAILED TO GIT ADD MAILEE"
    exit 1
fi

# git commit
sudo git commit -m "push mail"
if [ $? -ne 0 ]
then
    echo "ERROR FAILED TO GIT COMMIT"
    exit 1
fi

# git push
git push origin main
if [ $? -ne 0 ]
then
    echo "ERROR FAILED TO GIT PUSH ORIGIN MAIN"
    exit 1
fi

cd /home/pi

sudo rm -rf /home/pi/push_email

exit 0
