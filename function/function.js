/**
 * 移动端判断ios、android系统
 * @author lilei
 * @return {string} 系统名称
 */
function mobileSystem() {
    var system = '';
    var userAgent = window.navigator.userAgent.toLowerCase();
    if (userAgent.match(/iphone|ipad|ipod/i)) {
        system = 'ios';
    } else if (userAgent.match(/android/i)) {
        system = 'android';
    }
    return system;
}

/**
 * 判断是否微信环境
 * @author lilei
 * @return {boolean}
 */
function isWeixin() {
    var userAgent = window.navigator.userAgent.toLowerCase();
    return userAgent.match(/MicroMessenger/i) ? true : false;
}

/**
 * 获取鼠标当前坐标
 * @author lilei
 * @param {event} e
 * @return {object} 鼠标位置坐标
 */
function getPosition(e) {
    // 考虑滚动条影响
    var scrollLeft = document.documentElement.scrollLeft || document.body.scrollLeft;
    var scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
    // 通过json返回x、y坐标
    return {x: e.clientX + scrollLeft, y: e.clientY + scrollTop};
}

/**
 * 获取日期时间
 * @author lilei
 * @param {string} time 基准时间默认当前时间（只有日期没有时间默认08:00:00）
 * @param {int} add 日期增量
 * @example time='2010-10-10 00:00:00'，add=-1前一天，add＝0当天，add＝1后一天
 * @return {string} 年-月-日 时:分:秒
 */
function getTime(time, add) {
    var date = time ? new Date(time) : new Date();
    var timestamp = Date.parse(date) + add * 24 * 3600 * 1000;
    date = new Date(timestamp);
    var year = date.getFullYear();
    var mouth = date.getMonth() + 1;
    var day = date.getDate();
    var hour = date.getHours();
    var minute = date.getMinutes();
    var second = date.getSeconds();
    mouth = mouth > 9 ? mouth : '0' + mouth;
    day = day > 9 ? day : '0' + day;
    hour = hour > 9 ? hour : '0' + hour;
    minute = minute > 9 ? minute : '0' + minute;
    second = second > 9 ? second : '0' + second;
    return year + '-' + mouth + '-' + day + ' ' + hour + ':' + minute + ':' + second;
}

/**
 * 倒计时
 * @author lilei
 * @param {int} time 倒计时秒数
 * @param {object} obj 显示倒计时元素对象
 * @param {function} callback 倒计时结束回调函数
 */
function showTime(time, obj, callback) {
    if (isNaN(time)) {
        if (callback) {
            callback();
        }
        return false;
    }
    var hour = Math.floor(time / 3600);
    var minute = Math.floor((time % 3600) / 60);
    var second = time % 60;
    time--;
    if (second < 0) {
        second += 60;
        minute--;
    }
    if (minute < 0) {
        minute += 60;
        hour--;
    }
    var hour1 = hour > 9 ? hour : '0' + hour;
    var minute1 = minute > 9 ? minute : '0' + minute;
    var second1 = second > 9 ? second : '0' + second;
    obj.innerHTML = hour1 + ':' + minute1 + ':' + second1;
    timer = setTimeout(function () {
        showTime(time, obj, callback);
    }, 1000);
    if (hour == 0 && minute == 0 && second == 0) {
        clearTimeout(timer);
        if (callback) {
            callback();
        }
    }
}

/**
 * 替换换行符
 * @author lilei
 * @param {string} content 内容
 * @return {string} 替换后的内容
 */
function lnToBr(content) {
    try {
        content = content.replace(/\r\n/g, '<br>');
        content = content.replace(/\n/g, '<br>');
    } catch (e) {
        alert(e.message);
    }
    return content;
}

/**
 * 多物体运动
 * @author lilei
 * @param {object} obj 运动对象
 * @param {json} json 运动属性和目标的json对象
 * @param {function} callback 运动完成后回调函数
 */
function move(obj, json, callback) {
    // 先清空定时器，防止连续点击速度变快
    clearInterval(obj.timer);
    obj.timer = setInterval(function () {
        // 假设所有属性都到目标点，设置一个标志变量
        var target = true;
        // 循环json中的参数，可多个属性同时运动
        for (var attr in json) {
            if (attr == 'opacity') {
                // 透明度
                var current = Math.round(parseFloat(getStyle(obj, attr)) * 100);
            } else {
                // 其他属性
                var current = parseInt(getStyle(obj, attr));
            }
            // 运动速度
            var speed = (json[attr] - current) / 5;
            // 速度取整
            speed = speed > 0 ? Math.ceil(speed) : Math.floor(speed);
            // 只要有一个属性没到目标点，不能清除定时器，把标志变量置假
            if (current != json[attr]) {
                target = false;
            }
            if (attr == 'opacity') {
                // ie
                obj.style[attr] = 'filter(opacity:' + (current + speed) + ')';
                // chrome、ff
                obj.style[attr] = (current + speed) / 100;
            } else {
                obj.style[attr] = current + speed + 'px';
            }
        }
        // 所有属性到达目标点，清除定时器
        if (target) {
            clearInterval(obj.timer);
            // 如果有函数参数传入就执行
            if (callback) {
                callback();
            }
        }
    }, 30);
}

/**
 * get方式发送ajax请求
 * @author lilei
 * @param {string} url 请求地址
 * @param {function} success 请求成功后回调函数
 * @param {function} error 请求失败后回调函数
 */
function get(url, success, error) {
    // 创建ajax对象
    if (window.XMLHttpRequest) {
        // 非ie6
        var obj = new XMLHttpRequest();
    } else {
        // ie6
        var obj = new ActiveXObject('Microsoft.XMLHTTP');
    }

    // 连接服务器，open(方法，请求地址，异步请求)
    obj.open('get', url, true);
    // 接收返回内容，浏览器与服务器交互状态变化事件
    obj.onreadystatechange = function () {
        // 0（未初始化）还没调用open()方法
        // 1（载入）已经调用send()方法，正在发送请求
        // 2（载入完成）send()方法完成，已经收到全部响应内容
        // 3（解析）正在解析响应内容
        // 4（完成）解析完成，可以在客户端使用
        // readyState浏览器和服务器交互状态，4读取完成
        if (obj.readyState == 4) {
            // status返回的http状态码，200处理成功
            if (obj.status == 200) {
                // responseText文本/json方式返回的内容
                success(obj.responseText);
            } else {
                if (error) {
                    error(obj.status);
                }
            }
        }
    }
    // 发送数据
    obj.send();
}

/**
 * post方式发送ajax请求
 * @author lilei
 * @param {string} url 请求地址
 * @param {string} data 数据
 * @param {function} success 请求成功后回调函数
 * @param {function} error 请求失败后回调函数
 */
function post(url, data, success, error) {
    // 创建ajax对象
    if (window.XMLHttpRequest) {
        // 非ie6
        var obj = new XMLHttpRequest();
    } else {
        // ie6
        var obj = new ActiveXObject('Microsoft.XMLHTTP');
    }

    // 连接服务器，open(方法，请求地址，异步请求)
    obj.open('post', url, true);
    // 设置发送请求的http头通过url编码
    obj.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    // 接收返回内容，浏览器与服务器交互状态变化事件
    obj.onreadystatechange = function () {
        // 0（未初始化）还没调用open()方法
        // 1（载入）已经调用send()方法，正在发送请求
        // 2（载入完成）send()方法完成，已经收到全部响应内容
        // 3（解析）正在解析响应内容
        // 4（完成）解析完成，可以在客户端使用
        // readyState浏览器和服务器交互状态，4读取完成
        if (obj.readyState == 4) {
            // status返回的http状态码，200处理成功
            if (obj.status == 200) {
                // responseText文本/json方式返回的内容
                success(obj.responseText);
            } else {
                if (error) {
                    error(obj.status);
                }
            }
        }
    }
    // 发送数据
    obj.send(data);
}