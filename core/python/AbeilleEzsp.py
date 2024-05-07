# Abeille plugin for Jeedom
# EmberZnet/EZSP daemon
# Tcharp38

import logging
import string
import sys
import os
import time
import datetime
import traceback
import re
import signal
from optparse import OptionParser
from os.path import join
import json
import argparse

# Note: This daemon is launched from '/var/www/html/plugins/Abeille/core/ajax'
# Adding "Abeille/core/python" to import path
abeilleCorePython = os.path.dirname(os.path.realpath(__file__))
# print("acp=%s" % (abeilleCorePython))
# print("path1: ", sys.path)
sys.path.append(abeilleCorePython+"/jeedom")
# print("path2: ", sys.path)

# try:
# 	from jeedom import *
# except ImportError:
# 	print("Error: importing module jeedom.jeedom")
# 	sys.exit(1)

def read_socket():
	global JEEDOM_SOCKET_MESSAGE
	if not JEEDOM_SOCKET_MESSAGE.empty():
		logging.debug("Message received in socket JEEDOM_SOCKET_MESSAGE")
		message = json.loads(jeedom_utils.stripped(JEEDOM_SOCKET_MESSAGE.get()))
		if message['apikey'] != _apikey:
			logging.error("Invalid apikey from socket: %s", message)
			return
		try:
			print ('read')
		except Exception as e:
			logging.error('Send command to demon error: %s' ,e)

def listen():
	jeedom_socket.open()
	try:
		while 1:
			time.sleep(0.5)
			read_socket()
	except KeyboardInterrupt:
		shutdown()

# ----------------------------------------------------------------------------

def handler(signum=None, frame=None):
	logging.debug("Signal %i caught, exiting...", int(signum))
	shutdown()

def shutdown():
	logging.debug("Shutdown")
	logging.debug("Removing PID file %s", _pidfile)
	try:
		os.remove(_pidfile)
	except:
		pass
	try:
		jeedom_socket.close()
	except:
		pass
	try:
		jeedom_serial.close()
	except:
		pass
	logging.debug("Exit 0")
	sys.stdout.flush()
	os._exit(0)

# ----------------------------------------------------------------------------

_log_level = "error"
_socket_port = 55009
_socket_host = 'localhost'
_device = 'auto'
_pidfile = '/tmp/demond.pid'
_apikey = ''
_callback = ''
_cycle = 0.3

parser = argparse.ArgumentParser(description='Abeille EZSP daemon')
parser.add_argument("--device", help="Device", type=str)
parser.add_argument("--loglevel", help="Log Level for the daemon", type=str)
parser.add_argument("--callback", help="Callback", type=str)
parser.add_argument("--apikey", help="Apikey", type=str)
parser.add_argument("--cycle", help="Cycle to send event", type=str)
parser.add_argument("--pid", help="Pid file", type=str)
parser.add_argument("--socketport", help="Port for Zigbee server", type=str)
parser.add_argument("--gtwport", help="Gateway port", type=str)
args = parser.parse_args()

if args.device:
	_device = args.device
if args.loglevel:
    _log_level = args.loglevel
if args.callback:
    _callback = args.callback
if args.apikey:
    _apikey = args.apikey
if args.pid:
    _pidfile = args.pid
if args.cycle:
    _cycle = float(args.cycle)
if args.socketport:
	_socketport = args.socketport
if args.gtwport:
	_gtwport = args.gtwport

_socket_port = int(_socket_port)

# jeedom_utils.set_log_level(_log_level)
level = logging.DEBUG
FORMAT = '[%(asctime)-15s] %(message)s'
logging.basicConfig(level=level, format=FORMAT, datefmt="%Y-%m-%d %H:%M:%S")

logging.info('>>> AbeilleEzsp starting')
logging.info('Log level: %s', _log_level)
logging.info('Socket port: %s', _socket_port)
logging.info('Socket host: %s', _socket_host)
logging.info('PID file: %s', _pidfile)
logging.info('Apikey: %s', _apikey)
logging.info('Device: %s', _device)
logging.info('Port: %s', _gtwport)

signal.signal(signal.SIGINT, handler)
signal.signal(signal.SIGTERM, handler)

# try:
# 	# jeedom_utils.write_pid(str(_pidfile))
# 	jeedom_socket = jeedom_socket(port=_socket_port,address=_socket_host)
# 	listen()
# except Exception as e:
# 	logging.error('Fatal error: %s', e)
# 	logging.info(traceback.format_exc())
# 	shutdown()