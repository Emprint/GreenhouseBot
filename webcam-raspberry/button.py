# -*- coding: utf-8 -*-
import signal
import sys
import os
from LCD.Adafruit_CharLCD import Adafruit_CharLCD
from LCD.Adafruit_MCP230xx import MCP230XX_GPIO
import Adafruit_DHT
import RPi.GPIO as GPIO
from threading import Timer
import time
import subprocess
import shlex

button_pin = 15
humidity_pin = 17 #BCM
lcd = None
timer = None
timeOut = 12.0
timerRunning = False

button_last_state = False


#https://www.raspberrypi.org/forums/viewtopic.php?f=32&t=28915
def registerCustomChar(addr, bytes):
	lcd.write4bits(lcd.LCD_SETCGRAMADDR + addr*8)
	for b in bytes:
		lcd.write4bits(b, True)
	time.sleep(0.1)
	
#Generate using this template http://www.quinapalus.com/hd44780udg.html
#Can store up to 8 custom chars	
def registerCustomChars():
	eacute = [0x2,0x4,0xe,0x11,0x1f,0x10,0xe,0x0] 	#\x00 or chr(0)
	egrave = [0x8,0x4,0xe,0x11,0x1f,0x10,0xe,0x0] 	#\x01 or chr(1)
	ecirc = [0x4,0xa,0xe,0x11,0x1f,0x10,0xe,0x0] 	#\x02 or chr(2)
	aacute = [0x8,0x4,0xe,0x1,0xf,0x19,0xf,0x0] 	#\x03 or chr(3)
	acirc = [0x4,0xa,0xe,0x1,0xf,0x19,0xf,0x0] 		#\x04 or chr(4)
	ugrave = [0x8,0x4,0x11,0x11,0x11,0x13,0xd,0x0] 	#\x05 or chr(5)
	
	chars = [eacute, egrave, ecirc, aacute, acirc, ugrave]
	addr = 0
	for char in chars:
		registerCustomChar(addr, char)
		addr += 1

def message(text, clear = True):
	global lcd
	if clear:
		clear()
			
	lcd.message(text)
				
def center(text, line = 0, clear = False):
	global lcd
	if clear:
		clear()
	lcd.setCursor(0, line)
	message(text.center(16), False)
		
def clear():
	global lcd
	lcd.clear()

def initButton():
	global button_pin
	GPIO.setmode(GPIO.BOARD)

	GPIO.setup(button_pin, GPIO.IN, pull_up_down=GPIO.PUD_UP)
	
def turnOff():
	global timerRunning
	global lcd
	clear()
	lcd.backlight(False)
	timerRunning = False
	

def testChars():
	global lcd
	clear()
	for char in range(215, 230):
		lcd.setCursor(0, 0)
		message(str(char) + ' : ' + chr(char), False)
		time.sleep(.5)


def pressButton():
	global timer, timerRunning, button_last_state
	
	if timerRunning == False:
		timerRunning = True
		timer = Timer(timeOut, turnOff)
		timer.start()
		lcd.backlight(True)
		clear()
		#temp1p = subprocess.Popen("awk -F \"t=\" '/t=/ {print $2/1000}' /sys/bus/w1/devices/28-01159132c0ff/w1_slave", stdout=subprocess.PIPE)
		#temp1 = temp1p.stdout.read()
		#retcode = temp1p.wait()
		temp1 = subprocess.check_output(shlex.split("""awk -F "t=" '/t=/ {print $2/1000}' /sys/bus/w1/devices/28-01159132c0ff/w1_slave"""), stderr=subprocess.STDOUT)
		temp2 = subprocess.check_output(shlex.split("""awk -F "t=" '/t=/ {print $2/1000}' /sys/bus/w1/devices/28-0115916762ff/w1_slave"""), stderr=subprocess.STDOUT)
		lcd.message("Temp ext.: " + str(temp1) + "\nTemp int.: " + str(temp2))
		time.sleep(3)
		clear()
		
		humidity, temperature = Adafruit_DHT.read_retry(11, humidity_pin)
		lcd.message("Humidit\x00:  " + str(humidity) + "%\nTemp boi.: " + str(temperature)+chr(223))
		
	elif button_last_state  == False:
		timerRunning = True
		timer.cancel()
		timer = Timer(timeOut, turnOff)
		timer.start()
		
		

def initLCD():
	global lcd
	bus = 1         # Note you need to change the bus number to 0 if running on a r$
	address = 0x20  # I2C address of the MCP230xx chip.
	gpio_count = 8  # Number of GPIOs exposed by the MCP230xx chip, should be 8 or $

	# Create MCP230xx GPIO adapter.
	mcp = MCP230XX_GPIO(bus, address, gpio_count)

	# Create LCD, passing in MCP GPIO adapter.
	lcd = Adafruit_CharLCD(pin_rs=1, pin_e=2, pins_db=[3,4,5,6], GPIO=mcp, pin_b=7)
	lcd.begin(16, 2)
	lcd.backlight(True)
	registerCustomChars()
	clear()
	center("Bonjour");
	center("St\x00phanie", 1)
	time.sleep(3)
	#testChars()
	pressButton()


def run_program():
	global button_pin, button_last_state
	print('Démarrage LCD + bouton')
	initLCD()
	initButton()
	
	while True:
		input_state = GPIO.input(button_pin)
		
		if input_state == False:
			pressButton()
			button_last_state = True
		else:
			button_last_state = False
			
		time.sleep(0.005)
		

def exit_gracefully(signum, frame):
	# restore the original signal handler as otherwise evil things will happen
	# in raw_input when CTRL+C is pressed, and our signal handler is not re-entrant
	signal.signal(signal.SIGINT, original_sigint)

	try:
		if raw_input("\nReally quit? (y/n)> ").lower().startswith('y'):
			quit()

	except KeyboardInterrupt:
		print("Ok ok, quitting")
		quit()

	# restore the exit gracefully handler here    
	signal.signal(signal.SIGINT, exit_gracefully)

def quit():
	global lcd
	lcd.clear()
	lcd.backlight(False)
	sys.exit(1)
        
if __name__ == '__main__':
	# store the original SIGINT handler
	original_sigint = signal.getsignal(signal.SIGINT)
	signal.signal(signal.SIGINT, exit_gracefully)
	run_program()
