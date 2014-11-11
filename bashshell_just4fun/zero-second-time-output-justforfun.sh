#!/bin/bash

#对所有通过shell远程登录的用户，每隔一分钟就发送一条整分钟消息，附带不死机保佑。目前仅在CentOS 7运行通过。
#注意：本bash脚本仅供娱乐，会扰乱所有当前远程登录用户的屏幕输出！使用或误用该脚本而出现各种编译失败系统出错管理员抓狂之类，本人概不负责。
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
