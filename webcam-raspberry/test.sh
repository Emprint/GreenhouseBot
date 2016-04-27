#!/bin/bash

humData=17 #Python GPIO
#humPower=0
#sleep 10
#gpio mode $humPower out
#gpio write $humPower 1

#sleep 10
test=`sudo /home/pi/Adafruit_Python_DHT/examples/AdafruitDHT.py 11 $humData`
#test2=`echo "$test" | grep Temp`
#test3=`echo $test | sed -n -e 's/.*Temp=([0-9]*).*/TEST=/p'`
Temp3=`echo $test | sed -e 's/Temp=\([0-9\.]*\)\*.*/\1/g'`
Hum1=`echo $test | sed -e 's/Temp=\([0-9\.]*\)\*\sHumidity=\([0-9\.]*\)\%/\2/g'`
echo $Temp3
echo $Hum1

#echo $humPower
#gpio write $humPower 0