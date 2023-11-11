#!/usr/bin/python3
import pyautogui as pg
import sys
import time
x = sys.argv[1]
time.sleep(5)
pg.write(x, interval=0.25)
del pg, sys, x
