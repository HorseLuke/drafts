#!/bin/bash

#������ͨ��shellԶ�̵�¼���û���ÿ��һ���Ӿͷ���һ����������Ϣ���������������ӡ�Ŀǰ����CentOS 7����ͨ����
#ע�⣺��bash�ű��������֣����������е�ǰԶ�̵�¼�û�����Ļ�����ʹ�û����øýű������ָ��ֱ���ʧ��ϵͳ�������Աץ��֮�࣬���˸Ų�����
#@author Horse Luke

function SHOW_TIME_TO_ALL_PTS()
{
for i in  `ls /dev/pts`
do
    if [[ $i =~ ^[0-9]+$ ]]; then
        echo "THE TIME NOW IS "$(date "+%Y-%m-%d %H:%M:%S")". PLEASE READ: Fo Zu Bao You, Yong Bu Si Ji." > /dev/pts/$i
    fi
done
}


while true
do

    if [[ $(date "+%S") = "00" ]]; then
        SHOW_TIME_TO_ALL_PTS
    fi

    sleep 1s
done
