#!/bin/bash

rm -rf /home/pi/push_email  /home/pi/.gitconfig  /home/pi/.git-credentials

git clone http://github.com/nsgw/emails.git  /home/pi/push_email
if [ $? -ne 0 ]
then
    echo "ERROR FAILED TO CLONE EMAILS REPO"
    exit 1
fi

cd /home/pi/push_email

# cp /home/pi/update-gateway/dot_gitconfig  /home/pi/.gitconfig
# if [ $? -ne 0 ]
# then
#     echo "ERROR FAILED TO COPY DOT GITCONFIG"
#     exit 1
# fi

# chown --verbose  root:root  /home/pi/.gitconfig
# if [ $? -ne 0 ]
# then
#     echo "ERROR FAILED TO CHOWN DOT GITCONFIG"
#     exit 1
# fi


# cp /home/pi/update-gateway/dot_git-credentials  /home/pi/.git-credentials
# if [ $? -ne 0 ]
# then
#     echo "ERROR FAILED TO COPY DOT GIT-CREDENTIALS"
#     exit 1
# fi

# chown --verbose  root:root  /home/pi/.git-credentials
# if [ $? -ne 0 ]
# then
#     echo "ERROR FAILED TO CHOWN DOT GIT-CREDENTIALS"
#     exit 1
# fi

# git config credential.helper  store
# if [ $? -ne 0 ]
# then
#     echo "ERROR"
#     exit 1
# fi

#git push https://github.com/nsgw/emails.git
# git push --repo https://nsgw:nanosense92@github.com/nsgw/emails.git
# if [ $? -ne 0 ]
# then
#     echo "ERROR FAILED TO PUSH TO REPO EMAILS"
#     exit
# fi

cp /home/pi/mailee  $(hostname)
if [ $? -ne 0 ]
then
    echo "ERROR FAILED TO COPY MAILEE"
    exit
fi

git add $(hostname)
if [ $? -ne 0 ]
then
    echo "ERROR FAILED TO GIT ADD MAILEE"
    exit
fi

git commit -m "push mail"
if [ $? -ne 0 ]
then
    echo "ERROR FAILED TO GIT COMMIT"
    exit
fi

#git push origin main
git push --repo https://nsgw:nanosense92@github.com/nsgw/emails.git
if [ $? -ne 0 ]
then
    echo "ERROR FAILED TO GIT PUSH ORIGIN MAIN"
    exit
fi

rm -rf /home/pi/push_email

