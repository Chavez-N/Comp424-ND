#!/bin/bash

#update package list and upgrade packages
sudo apt update && sudo apt upgrade -y

#install apache
sudo apt install apache2 -y

# start apache service and enable it to run on boot
sudo systemctl start apache2
sudo systemctl enable apache2

#install MySQL
sudo apt install mysql-server -y


#setup MySQL (basic config)
  MYSQL_ROOT_PASSWORD='Password@123'
  MYSQL=$(grep 'temporary password' /var/log/mysqld.log | awk '{print $11}')

  SECURE_MYSQL=$(expect -c "

  set timeout 10
  spawn mysql_secure_installation

  expect \"Enter password for user root:\"
  send \"$MYSQL\r\"
  expect \"New password:\"
  send \"$MYSQL_ROOT_PASSWORD\r\"
  expect \"Re-enter new password:\"
  send \"$MYSQL_ROOT_PASSWORD\r\"
  expect \"Change the password for root ?\ ((Press y\|Y for Yes, any other key for No) :\"
  send \"y\r\"
  send \"$MYSQL\r\"
  expect \"New password:\"
  send \"$MYSQL_ROOT_PASSWORD\r\"
  expect \"Re-enter new password:\"
  send \"$MYSQL_ROOT_PASSWORD\r\"
  expect \"Do you wish to continue with the password provided?\(Press y\|Y for Yes, any other key for No) :\"
  send \"y\r\"
  expect \"Remove anonymous users?\(Press y\|Y for Yes, any other key for No) :\"
  send \"y\r\"
  expect \"Disallow root login remotely?\(Press y\|Y for Yes, any other key for No) :\"
  send \"n\r\"
  expect \"Remove test database and access to it?\(Press y\|Y for Yes, any other key for No) :\"
  send \"y\r\"
  expect \"Reload privilege tables now?\(Press y\|Y for Yes, any other key for No) :\"
  send \"y\r\"
  expect eof
  ")

  echo $SECURE_MYSQL

# Secure MySQL installation
sudo mysql_secure_installation

#install php and necessary modules
sudo apt install php libapache2-mod-php php-mysql -y

#restart apache to load php
sudo systemctl restart apache2

#install openssh server
sudo apt install openssh-server -y

#start openssh and enable it to run on boot
sudo systemctl start ssh
sudo systemctl enable ssh

#install snort
sudo apt install snort -y

#basic snort configuration
sudo sed -i 's/# ipvar HOME_NET any/ipvar HOME_NET 192.168.1.0\/24/' /etc/snort/snort.conf
sudo sed -i 's/# ipvar EXTERNAL_NET any/ipvar EXTERNAL_NET !$HOME_Net/' /etc/snort/snort.conf

#restart snort to apply
sudo systemctl restart snort

#install IPtables
sudo apt install iptables -y

#allow necessary ports through firewall
sudo iptables -A INPUT -p tcp --dport 22 -j ACCEPT # Allow SSH
sudo iptables -A INPUT -p tcp --dport 80 -j ACCEPT # allot http
sudo iptables -A INPUT -p tcp --dport 443 -j ACCEPT #allow https
sudo iptables -A INPUT -m conntrack --cstate ESTABLISHED,RELATED -j ACCEPT # allow established connections
sudo iptables -A INPUT -j DROP #drop all other traffic

#save iptables rule
sudo iptables-save| sudo tee /etc/iptables/rules.v4
echo "installation and configuration completed"

