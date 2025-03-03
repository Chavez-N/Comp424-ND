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

#secure mysql installation
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
