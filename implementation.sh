#!/bin/bash

# load iptables rules
sudo iptables-restore < /etc/iptables/rules.v4

#start snort
sudo systemctl enable snort

#enable snort to run on boot
sudo systemctl enable snort

#check the status of services
echo "checking the status of apache, mysql, ssh, and snort services..."
#simplifies function checking
check_service_status() {
systemctl status "$1" > /dev/null 2>&1
return $?
}
return_service_status() {
if [$status -eq 0 ]; then
	echo"service is running"
else
	echo"service is not running or an error occured"
fi
}

check_service_status apache2
status=$?
return_service_status apache2



check_service_status mysql
status=$?
return_service_status mysql



check_service_status ssh
status=$?
return_service_status ssh


check_service_status snort
status=$?
return_service_status snort

echo"firewall and IDS policies have been implemented"

