#!/usr/bin/expect
spawn su root
expect "Password:"
send "super#321\n"
#send "su - www\n"
send "cd /home/wwwroot/hangowa.com/v2/ \n"
send "git clean -f -d > /dev/null \n"
send "git fetch origin \n"
send "git reset origin/master \n"
send "git checkout -f \n"
#send "git pull origin master \n"
#spawn su root
#expect "Password:"
#send "super#321\n"
send "chown -Rf nobody:nobody /home/wwwroot/hangowa.com/v2/src/\n"
send "chmod -Rf 755 /home/wwwroot/hangowa.com/v2/deployment.sh \n"
expect eof
exit
