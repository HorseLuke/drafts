@echo off

echo ������...


echo %date:~0,10% %time:~0,8% > testdata\1.txt
python duplicate_zip_entry.py testdata.zip testdata\1.txt

echo %date:~0,10% %time:~0,8% > testdata\1.txt
python duplicate_zip_entry.py testdata.zip testdata\1.txt


echo %date:~0,10% %time:~0,8% > testdata\1.txt
python duplicate_zip_entry.py testdata.zip testdata\1.txt

echo ���н���...

pause