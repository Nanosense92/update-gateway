#!/bin/sh

bash /home/pi/enocean-gateway/is_raspi.bash
if [ $? -ne 0 ]
then
    exit 0;
fi

# add the line in /boot/config.txt to enable the usb boot mode
grep "program_usb_boot_mode" /boot/config.txt
if [ $? -gt 0 ]
then
    echo "program_usb_boot_mode=1" | tee -a /boot/config.txt
fi

# raise the swap size to 1024M because 100M is not enough
sed -ri 's/CONF_SWAPSIZE=[[:digit:]]*/CONF_SWAPSIZE=1024/' /etc/dphys-swapfile

# enable swap if memory available in RAM is < 10%
grep "vm.swappiness" /etc/sysctl.conf
if [ $? -gt 0 ]
then
    echo "" | tee -a /etc/sysctl.conf
    echo "###################################################################" | tee -a /etc/sysctl.conf
    echo "#SWAP handling" | tee -a /etc/sysctl.conf
    echo "vm.swappiness = 10" | tee -a /etc/sysctl.conf
fi

# enable cron log file + separate critic messages in multiple log files according to their priority
sed -ri 's/\#(cron\.\*[\s]*.*)/\1/' /etc/rsyslog.conf
grep "# save critic messages in different files" /etc/rsyslog.conf
if [ $? -gt 0 ]
then
    echo "" | tee -a /etc/rsyslog.conf
    echo "# save critic messages in different files" | tee -a /etc/rsyslog.conf
    echo "*.emerg   /var/log/emerg" | tee -a /etc/rsyslog.conf
    echo "*.alert   /var/log/alert" | tee -a /etc/rsyslog.conf
    echo "*.crit    /var/log/crit" | tee -a /etc/rsyslog.conf
    echo "*.err     /var/log/err" | tee -a /etc/rsyslog.conf
    echo "# save boot messages in boot.log" | tee -a /etc/rsyslog.conf
    echo "local7.*  /var/log/boot.log" | tee -a /etc/rsyslog.conf
fi

# use tmpfs for /tmp and /var/tmp to mount them in RAM instead of the SD card (to improve its life span)
sed -ri '/tmpfs\ *\/tmp\/jeedom\ *tmpfs\ *.*/d' /etc/fstab
grep -P "tmpfs[\s]*\/tmp[\s]*tmpfs.*" /etc/fstab
if [ $? -gt 0 ]
then
    echo "tmpfs             /tmp        tmpfs   defaults,noatime,nosuid,size=250M 0 0" | tee -a /etc/fstab
    echo "tmpfs             /var/tmp    tmpfs   defaults,noatime,nosuid,size=100M 0 0" | tee -a /etc/fstab
fi

