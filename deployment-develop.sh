#!/usr/bin/expect
spawn su root
expect "Password:"
#send "super#321\n"
#send "su - www\n"
send "cd /home/wwwroot/hangowa.com/hango2/ \n"
send "git clean -f -d > /dev/null \n"
send "git fetch origin \n"
send "git reset --hard origin/develop \n"
#send "git pull origin master \n"
#spawn su root
#expect "Password:"
#send "super#321\n"
send "chown -Rf www:www /home/wwwroot/hangowa.com/hango2/src/\n"
send "chmod -Rf 755 /home/wwwroot/hangowa.com/hango2/deployment-develop.sh \n"
expect eof
exit