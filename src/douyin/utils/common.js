
//获取时间戳
function getTimestamp() {
    return Date.parse(new Date()) / 1000;
}

function getTime(time) {
    var hour = parseInt(time / 3600);
    if (hour < 10) {
        hour = '0' + hour;
    }
    var fen = parseInt((time - hour * 3600) / 60);
    if (fen < 10) {
        fen = '0' + fen;
    }
    var second = time - hour * 3600 - fen * 60;
    if (second < 10) {
        second = '0' + second;
    }
    var aa = [];
    aa.push(hour);
    aa.push(fen);
    aa.push(second);
    // console.log(aa);
    return aa;
}

function XsgetTime(time) { //限时 天数
    var day = parseInt(time / (60 * 60 * 24));
    var day1=0;
    var day2=0;
    if (day < 10) {
        day1 = 0;
        day2 = day;
    }else{
        day1 = parseInt(day / 10);
        day2 = day % 10
    }
    var hour = parseInt((time % (60 * 60 * 24)) / (60 * 60));
    var hour1 =0;
    var hour2 = 0;
    if (hour < 10) {
        hour1 = 0;
        hour2 = hour;
    } else {
        hour1 = parseInt(hour / 10);
        hour2 = hour % 10
    }
    var fen = parseInt((time % (60 * 60)) / (60));
    var fen1 = 0;
    var fen2 = 0;
    if (fen < 10) {
        fen1 = 0;
        fen2 = fen;
    } else {
        fen1 = parseInt(fen / 10);
        fen2 = fen % 10
    }
    var second = (time % (60));
    var second1 = 0;
    var second2 = 0;
    if (second < 10) {
        second1 = 0;
        second2 = second;
    } else {
        second1 = parseInt(second / 10);
        second2 = second % 10
    }
    var aa = [];
    aa.push(day1)
    aa.push(day2)
    aa.push(hour1)
    aa.push(hour2);
    aa.push(fen1);
    aa.push(fen2);
    aa.push(second1);
    aa.push(second2);
    return aa;
}

module.exports = {
    getTimestamp: getTimestamp,
    getTime: getTime,
    XsgetTime: XsgetTime
}