#!/bin/bash
dirpath=`dirname $0`
file="$dirpath/webcam.jpg"
lock="$dirpath/working.lock"
token="your-private-token"
url="http://www.yourwebsite.com/your-picture-upload-script.php"
quality=30
gpio1=28
gpio2=29
let "tempLoop = 0"
let "tempLoopReset = 10" #Send the temps each 5 loops, so each 30 minutes
urlTemp="http://www.yourwebsite.com/your-temperature-upload-script.php"
sonde1="28-01159132c0ff"
sonde2="28-0115916762ff"
humData=17 #BCM Pin numbering for Python

#Remove lock file after restart if something went wrong
rm $lock

sleep 5 #Wait after boot
while [ true ]; do

if [ ! -f $lock ]; then
	echo "Creating lock file"
	touch $lock

	echo "Taking picture"
	gpio mode $gpio1 out
	gpio mode $gpio2 out
	gpio write $gpio1 1
	gpio write $gpio2 1
#	gpio readall
	
	#-vf -hf 
	raspistill -w 1280 -h 720 -q $quality -o $file
	
	gpio write $gpio1 0
	gpio write $gpio2 0
#	gpio readall	
	
	echo "Sending picture"
	curl --progress-bar --form raspifile=@$file --form token=$token $url
	echo ""
	
	if [ $tempLoop == 0 ]; then
		echo "Sending temp"
		#DS18B20 Temperature Sensors
		temp1=`awk -F "t=" '/t=/ {print $2/1000}' /sys/bus/w1/devices/$sonde1/w1_slave`
		temp2=`awk -F "t=" '/t=/ {print $2/1000}' /sys/bus/w1/devices/$sonde2/w1_slave`
		
		#if temp = 85 then wait a bit for the sensors to be ready and try again
		if [$temp1 == 85] || [$temp2 == 85]; then
			sleep 5
			temp1=`awk -F "t=" '/t=/ {print $2/1000}' /sys/bus/w1/devices/$sonde1/w1_slave`
			temp2=`awk -F "t=" '/t=/ {print $2/1000}' /sys/bus/w1/devices/$sonde2/w1_slave`
		fi
		
		#DHT11 Temperature and Humidity sensor
		dht11=`sudo /home/pi/Adafruit_Python_DHT/examples/AdafruitDHT.py 11 $humData`
		temp3=`echo $dht11 | sed -e 's/Temp=\([0-9\.]*\)\*.*/\1/g'`
		hum1=`echo $dht11 | sed -e 's/Temp=\([0-9\.]*\)\*\sHumidity=\([0-9\.]*\)\%/\2/g'`

		curl --progress-bar --form data[]=$temp1 --form data[]=$temp2 --form data[]=$temp3 --form data[]=$hum1 --form token=$token $urlTemp
		echo ""
	fi
	
	let "tempLoop = tempLoop + 1"
	if [ $tempLoop == $tempLoopReset ]; then
		let "tempLoop = 0"
	fi

	echo "Removing lock file"
	rm $lock
else
	echo "Lock file still present"
fi

echo "Sleeping 3 minutes"
sleep 175s

#echo "Sleeping 5 minutes"
#sleep 60
#
#echo "Sleeping 4 minutes"
#sleep 60
#
#echo "Sleeping 3 minutes"
#sleep 60
#
#echo  "Sleeping 2 minutes"
#sleep 60
#
#echo "Sleeping 30 secondes"
#sleep 30

done
