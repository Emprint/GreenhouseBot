# GreenhouseBot
A greenhouse raspberry pi device creating timelapse videos and gathering temperature and humidity

It's using a Pi NoIR camera and two IR LEDs powered by GPIO 28 and 29 (WiringPi numbering, so BCM 20 and 21, or physical ports 38 and 40)<br>
It's also using two DS18B20 Temperature Sensors and one DHT11 Temperature and Humidity sensor<br>
The DHT11 data port is wired in GPIO 0 (WiringPi numbering, so it's BCM 17 or physical 11)<br>
There is a 16x02 LCD display on I2C backpack, from Adafruit and a button displaying sensors data on the LCD. The button is wired on GPIO 3 (BCM 22, physical 15)<br>
DS18B20 Temperature sensors are using 1-wire so you need to enable the 1-Wire library, like so http://www.raspberrypi-spy.co.uk/2013/03/raspberry-pi-1-wire-digital-thermometer-sensor/<br>
You can connect a lot of sensors like this using only one GPIO on the Raspberry which is really cool!

The PI scripts require that you install the DHT library from Adafruit https://github.com/adafruit/Adafruit_Python_DHT

Pictures taken by the camera and temperatures are sent to a webserver running PHP and storing data in MySQL. The webserver needs also to run ffmpeg to convert pictures once a week into a video.

Change your private password into both webcam-www/config.inc.php and webcam-raspberry/script.sh

Video generation is done by webcam-www/timelapse.sh by a cron job like this<br>
0 3 * * 0 /path/to/script/timelapse.sh  #Weekly timelapse video generation

Pi scripts are executed at startup in a screen by being added to /etc/rc.local like this<br>
su pi -c "screen -dm -S webcam ~/webcam/script.sh"<br>
su pi -c "screen -dm -S lcd sudo python ~/webcam/button.py"
