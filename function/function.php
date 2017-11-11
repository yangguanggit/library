<?php
/**
 * 数据过滤
 * @author lilei
 * @param int|string|array $value 输入值
 * @return int|string|array 数字不变，字符串加单引号，特殊字符转义
 */
function filter($value){
    if(is_array($value)){
        foreach($value as $k => $v){
            if(is_numeric($v)){
                continue;
            }
            if(get_magic_quotes_gpc()){
                $v = stripslashes($v);
            }
            $v = trim($v);
            $v = mysql_escape_string($v);
            $v = htmlspecialchars($v);
            $value[$k] = "'$v'";
        }
        return $value;
    }else{
        if(is_numeric($value)){
            return $value;
        }
        if(get_magic_quotes_gpc()){
            $value = stripslashes($value);
        }
        $value = trim($value);
        $value = mysql_escape_string($value);
        $value = htmlspecialchars($value);
        return "'$value'";
    }
}

/**
 * 正则校验
 * @author lilei
 * @param int|string $value 输入值
 * @param string $type 类型
 * @return bool|array 数组包含匹配到的信息
 */
function check($value, $type){
	switch($type){
		case 'id':
			// 身份证号码
			if(preg_match('/^([1-9]\d{5})(\d{4})(\d{2})(\d{2})\d{3}[\dX]$/', $value, $match)){
				return $match;
			}else{
				return false;
			}
			break;
		case 'mobile':
			// 手机号码
			if(preg_match('/^1[34578]\d{9}$/', $value)){
				return true;
			}else{
				return false;
			}
			break;
		case 'phone':
			// 电话号码
			if(preg_match('/^\d{3}-\d{8}|\d{4}-\d{7,8}$/', $value)){
				return true;
			}else{
				return false;
			}
			break;
		case 'qq':
			// qq号码
			if(preg_match('/^[1-9]\d{4,11}$/', $value)){
				return true;
			}else{
				return false;
			}
			break;
		case 'email':
			// 邮箱
			if(preg_match('/^\w+(?:[.-]?\w+)*@\w+(?:[.-]?\w+)*\.[a-zA-Z]+$/', $value)){
				return true;
			}else{
				return false;
			}
			break;
		case 'url':
			// 网址
			if(preg_match('/^(https?):\/\/([^\s\/:]+)(:\d+)?(\S*)$/', $value, $match)){
				return $match;
			}else{
				return false;
			}
			break;
		case 'html':
			// html标签
			if(preg_match('/^<(\w+)[^>]*>.*?<\/\1>$|^<\w+[^>]*>$/', $value)){
				return true;
			}else{
				return false;
			}
			break;
		default:
			return false;
			break;
	}
}

/**
 * 文件日志
 * @author lilei
 * @param string|array $data 日志数据
 * @param string $name 日志文件名
 * @return null
 */
function fileLog($data, $name){
	$path = $_SERVER['DOCUMENT_ROOT'].'/log';
	if(!is_dir($path)){
	    mkdir($path, 0777, true);
	    chmod($path, 0777);
	}
	$file = fopen($path.'/'.$name, 'a');
	fwrite($file, date('Y-m-d H:i:s').PHP_EOL);
	if(is_array($data)){
		foreach($data as $k => $v){
			fwrite($file, $k.':'.$v.';');
		}
	}else{
		fwrite($file, $data);
	}
	fwrite($file, PHP_EOL.PHP_EOL);
	fclose($file);
}

/**
 * 读取文件夹下文件列表
 * @author lilei
 * @param string $path 文件夹路径
 * @return array 文件夹下文件列表
 */
public function readDirectory($path){
    $files = array();
    if($directory=opendir($path)){
        while($file=readdir($directory)){
            if($file!='.' && $file!='..' && $file!='.DS_Store'){
                $files[] = $path.'/'.$file;
            }
        }
        closedir($directory);
    }
    return $files;
}

/**
 * 多维数组转一维数组
 * @author lilei
 * @param array $array 多维数组
 * @return array 一维数组
 */
function oneDimensional($array){
    $result = array();
    array_walk_recursive($array, function($v, $k) use (&$result){
        if(is_numeric($k)){
            $result[] = $v;
        }else{
            $result[$k] = $v;
        }
    });
    return  $result;
}

/**
 * 计算时间差
 * @author lilei
 * @param int $time 指定时间戳
 * @return string 时间差
 */
function timeDiff($time){
	$time = time()-$time;
	$text = $time>0 ? '前' : '后';
	$time = abs($time);
    $unit = array(
		'31536000'=>'年',
		'2592000'=>'个月',
		'604800'=>'周',
		'86400'=>'天',
		'3600'=>'小时',
		'60'=>'分钟',
		'1'=>'秒'
    );
	foreach($unit as $k => $v){
		$result = floor($time/(int)$k);
		if($result != 0){
			return $result.$v.$text;
		}
	}
}

/**
 * 最后一次登陆时间
 * @author lilei
 * @param null
 * @return bool|string 第一次登陆返回false，非第一次返回登陆时间
 */
function lastLogin(){
	if(empty($_COOKIE['lastLogin'])){
        setcookie('lastLogin', date('Y-m-d H:i:s'), time()+30*24*3600);
        return false;
	}else{
        $lastLogin = $_COOKIE['lastLogin'];
        setcookie('lastLogin', date('Y-m-d H:i:s'), time()+30*24*3600);
        return $lastLogin;
	}
}

/**
 * 清除cookie
 * @author lilei
 * @param null
 * @return null
 */
function clearCookie(){
    foreach($_COOKIE as $k => $v){
        setcookie($k, '', time()-1);
    }
    echo '<pre>';
    var_dump($_COOKIE);
}

/**
 * file_get_contents模拟post请求
 * @author lilei
 * @param string $url 请求地址（完整url）
 * @param array $params 数据
 * @return string 响应数据
 */
function filePost($url, $params=array()){
    $option = array(
        'http'=>array(
            'method'=>'POST',
            'header'=>"Content-Type:application/x-www-form-urlencoded",
            'content'=>http_build_query($params)
        )
    );
    $response = file_get_contents($url, false, stream_context_create($option));
    return trim($response);
}

/**
 * curl模拟post请求
 * @author lilei
 * @param string $url 请求地址（完整url）
 * @param array $params 数据
 * @return string 响应数据
 */
function curlPost($url, $params=array()){
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_TIMEOUT, 30);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
	$response = curl_exec($curl);
	curl_close($curl);
	return trim($response);
}

/**
 * fsockopen模拟post请求
 * @author lilei
 * @param string $url 请求地址（完整url）
 * @param array $params 数据
 * @return string 响应数据
 */
function sockPost($url, $params=array()){
	$response = '';
	$parse = parse_url($url);
	$scheme = $parse['scheme'];
	$host = $parse['host'];
	$port = $parse['port'] ? $parse['port'] : 80;
	$path = $parse['path'];
	$params = http_build_query($params);
	// 打开连接
	$file = fsockopen($host, $port, $errno, $errstr, 30) or die('连接失败');
	// http请求头
	$header = "POST $path HTTP/1.0\r\n";
	$header .= "Host: $host\r\n";
	$header .= "Referer: $url\r\n";
	$header .= "Content-type: application/x-www-form-urlencoded\r\n";
	$header .= 'Content-Length: '.strlen($params)."\r\n";
	$header .= "\r\n";
	$header .= $params;
	fputs($file, $header);
	// http响应头（读到头部和响应数据之间换行停止）
	$header = '';
	while($buffer=trim(fgets($file, 1024))){
    	$header .= $buffer;
	}
	// 响应数据
	while(!feof($file)){
		$response .= fgets($file, 1024);
	}
	// 关闭连接
	fclose($file);
	return trim($response);
}

/**
 * 文件上传
 * @author lilei
 * @param string $name 表单file控件名
 * @param string $type 文件类型
 * @param int $max 文件大小限制（单位M）
 * @param string $dir 子文件夹名
 * @return array|string 成功返回文件信息数组，失败返回错误信息
 */
function upload($name, $type='all', $max=2, $dir=''){
    $image = array('png', 'jpg', 'gif', 'jpeg');
    $document = array('txt', 'doc', 'xls', 'ppt', 'pdf', 'docx', 'xlsx', 'pptx');
    $compress = array('zip', 'rar');

    if(empty($_FILES[$name])){
        return '数据错误';
    }
    $file = $_FILES[$name];
    $error = $file['error'];
    $tempName = $file['tmp_name'];
    $fileType = $type;
    if(is_uploaded_file($tempName)){
        $name = $file['name'];
        $info = pathinfo($name);
        $ext = strtolower($info['extension']);
        $size = $file['size'];

        // 校验
        if($size>$max*1024*1024 || $size==0){
            $error = '文件不能大于'.$max.'M，请重新上传';
        }
        if($type == 'all'){
            if(in_array($ext, $image)){
                $fileType = 'image';
            }else if(in_array($ext, $document)){
                $fileType = 'document';
            }else if(in_array($ext, $compress)){
                $fileType = 'compress';
            }else{
                $error = '文件格式不正确，请上传图片、文档、压缩文件';
            }
        }else if($type=='image' && !in_array($ext, $image)){
            $error = '文件格式不正确，请上传图片';
        }else if($type=='document' && !in_array($ext, $document)){
            $error = '文件格式不正确，请上传文档';
        }else if($type=='compress' && !in_array($ext, $compress)){
            $error = '文件格式不正确，请上传压缩文件';
        }
        if($error){
            return $error;
        }
        
        // 生成随机文件名
        $newName = date('YmdHis').mt_rand(10, 99).$name;
        // 上传路径
        $path = $_SERVER['DOCUMENT_ROOT'].'/upload';
        // 访问地址
        $url = '/upload';
        if($dir){
            $path .= '/'.$dir;
            $url .= '/'.$dir;
        }
        if(!is_dir($path)){
            mkdir($path, 0777, true);
            chmod($path, 0777);
        }
        $path .= '/'.$newName;
        $url .= '/'.$newName;

        if(move_uploaded_file($tempName, $path)){
            return array('name'=>$name, 'type'=>$fileType, 'url'=>$url);
        }else{
            return $error;
        }
    }else{
        return $error;
    }
}

/**
 * 多文件上传
 * @author lilei
 * @param string $name 表单file控件名
 * @param string $type 文件类型
 * @param int $max 文件大小限制（单位M）
 * @param string $dir 子文件夹名
 * @return array|string 成功返回文件信息数组，失败返回错误信息
 * @example <input type="file" name="file[]" multiple>
 */
function multipleUpload($name, $type='all', $max=2, $dir=''){
    $image = array('png', 'jpg', 'gif', 'jpeg');
    $document = array('txt', 'doc', 'xls', 'ppt', 'pdf', 'docx', 'xlsx', 'pptx');
    $compress = array('zip', 'rar');

    if(empty($_FILES[$name])){
        return '数据错误';
    }
    $file = $_FILES[$name];
    // 转换格式
    $array = array();
    foreach($file as $k => $v){
        foreach($v as $i => $j){
            $array[$i][$k] = $j;
        }
    }
    $file = $array;
    
    $result = array();
    $success = count($file);
    $fail = 0;
    foreach($file as $v){
        $error = $v['error'];
        $tempName = $v['tmp_name'];
        $fileType = $type;
        if(is_uploaded_file($tempName)){
            $name = $v['name'];
            $info = pathinfo($name);
            $ext = strtolower($info['extension']);
            $size = $v['size'];

            // 校验
            if($size>$max*1024*1024 || $size==0){
                $error = '文件不能大于'.$max.'M，请重新上传';
            }
            if($type == 'all'){
                if(in_array($ext, $image)){
                    $fileType = 'image';
                }else if(in_array($ext, $document)){
                    $fileType = 'document';
                }else if(in_array($ext, $compress)){
                    $fileType = 'compress';
                }else{
                    $error = '文件格式不正确，请上传图片、文档、压缩文件';
                }
            }else if($type=='image' && !in_array($ext, $image)){
                $error = '文件格式不正确，请上传图片';
            }else if($type=='document' && !in_array($ext, $document)){
                $error = '文件格式不正确，请上传文档';
            }else if($type=='compress' && !in_array($ext, $compress)){
                $error = '文件格式不正确，请上传压缩文件';
            }
            if($error){
                $success--;
                $fail++;
                continue;
            }
            
            // 生成随机文件名
            $newName = date('YmdHis').mt_rand(10, 99).$name;
            // 上传路径
            $path = $_SERVER['DOCUMENT_ROOT'].'/upload';
            // 访问地址
            $url = '/upload';
            if($dir){
                $path .= '/'.$dir;
                $url .= '/'.$dir;
            }
            if(!is_dir($path)){
                mkdir($path, 0777, true);
                chmod($path, 0777);
            }
            $path .= '/'.$newName;
            $url .= '/'.$newName;

            if(move_uploaded_file($tempName, $path)){
                $result['data'][] = array('name'=>$name, 'type'=>$fileType, 'url'=>$url);
            }else{
                $success--;
                $fail++;
                continue;
            }
        }else{
            $success--;
            $fail++;
            continue;
        }
    }
    
    $result['success'] = $success;
    $result['fail'] = $fail;
    return $result;
}

/**
 * 画图验证码（需要安装gd库）
 * @author lilei
 * @param null
 * @return null
 */
function verifyCode(){
	// 设置http头
    header("Expires:-1");
    header("Pragma:no-cache");
    header("Cache-Control:no-cache,must-revalidate");
	header("Content-Type:image/png");
	session_start();

	$string = '23456789abcdefghkmnpqrstuvwxyzABCDEFGHKMNPQRSTUVWXYZ';
	$code = '';
	$length = 4;
	$width = 150;
	$height = 50;
	$font = __DIR__.'/font/Elephant.ttf';
	$fontSize = 20;
	$average = $width/$length;

	// 随机生成验证码
	for($i=0; $i<$length; $i++){
        $code .= $string[mt_rand(0, strlen($string)-1)];
    }
	// 设置验证码session
	$_SESSION['verify_code'] = strtolower($code);
	// 新建画布
	$image = imagecreatetruecolor($width, $height);
	// 填充背景
	$background = imagecolorallocate($image, 250, 250, 250);
	imagefill($image, 0, 0, $background);
	// 画出验证码
	if(function_exists('imagettftext')){
		for($i=0; $i<$length; $i++){
			$color = imagecolorallocate($image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
			// 同时需要gd库和freetype库
			imagettftext($image, $fontSize, mt_rand(-30, 30), $i*$average+mt_rand(1, 5), $height/1.5, $color, $font, $code[$i]);
		}
	}else{
		for($i=0; $i<$length; $i++){
			$color = imagecolorallocate($image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
        	imagestring($image, 5, $i*$average+mt_rand(5, 10), $height/3, $code[$i], $color);
		}
	}
	// 画出干扰线
	for($i=0; $i<5; $i++){
		$color = imagecolorallocate($image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
		imageline($image, mt_rand(0, $width), mt_rand(0, $height), mt_rand(0, $width), mt_rand(0, $height), $color);
	}
	// 显示验证码图片
	imagepng($image);
	// 销毁画布
	imagedestroy($image);
}

/**
 * php多进程（需要安装pcntl扩展）
 * @author lilei
 * @param string $name 执行函数名
 * @param array $params 函数参数
 * @param int $number 创建进程数
 * @return null
 */
function daemon($name, $params, $number){
	while(true){
		$pid = pcntl_fork();
		if($pid == -1){
			die('创建进程失败');
		}else if($pid){
			// 创建的子进程
			static $i = 0;
			$i++;
			if($i >= $number){
				// 当进程数量到达一定数量时，回收子进程
				pcntl_wait($status);
				$i--;
			}
		}else{
			// 0代表是子进程创建的，进入工作状态
			if(function_exists($name)){
				while(true){
					$posixPid = posix_getpid();
					call_user_func_array($name, $params);
					sleep(1);
				}
			}else{
				die('函数不存在');
			}
			die();
		}
	}
}

/**
 * 命令行获取用户输入
 * @author lilei
 * @param string $text 提示信息
 * @return string 用户输入
 */
function cliInput($text){
	// 提示用户输入
	fputs(STDOUT, $text);
	// 获取用户输入
	$input = trim(fgets(STDIN));
	return $input;
}

/**
 * socket服务端
 * @author lilei
 * @param string $host 服务器地址
 * @param int $port 服务器端口号
 * @param string|function $callback 回调函数（根据请求数据返回处理结果）
 * @return null
 * @example
 * server('127.0.0.1', '1234', function ($request){
 *     return $request;
 * });
 * @socket_create
 * @socket_bind
 * @socket_listen
 * @socket_accept
 * @socket_read
 * @socket_write
 * @socket_close
 */
function server($host, $port, $callback=null){
    // 忽略脚本超时
    set_time_limit(0);

    // 创建socket
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die('socket创建失败'.socket_strerror(socket_last_error())."\n");
    // 绑定端口
    socket_bind($socket, $host, $port) or die('socket绑定失败'.socket_strerror(socket_last_error())."\n");
    // 开始监听
    socket_listen($socket, 10) or die('socket监听失败'.socket_strerror(socket_last_error())."\n");

    while(true){
        // 另一个socket处理通信，阻塞状态，等待接受客户端请求
        $dialog = socket_accept($socket) or die('接受请求失败'.socket_strerror(socket_last_error())."\n");
        // 读取数据
        $request = socket_read($dialog, 1024) or die('读取数据失败'.socket_strerror(socket_last_error())."\n");
        $request = trim($request);
        // 回调函数
        if(is_callable($callback)){
            $response = call_user_func($callback, $request);
        }else{
            $response = 'success';
        }
        // 写入数据
        socket_write($dialog, $response, strlen($response)) or die('写入数据失败'.socket_strerror(socket_last_error())."\n");
        // 关闭socket
        socket_close($dialog);
    }

    // 关闭socket
    socket_close($socket);
}

/**
 * socket客户端
 * @author lilei
 * @param string $host 服务器地址
 * @param int $port 服务器端口号
 * @param string $request 请求数据
 * @param string|function $callback 回调函数（根据响应数据做后续处理）
 * @return null
 * @example
 * server('127.0.0.1', '1234', 'hello world', function ($response){
 *     echo $response;
 * });
 * @socket_create
 * @socket_connect
 * @socket_write
 * @socket_read
 * @socket_close
 */
function client($host, $port, $request='test', $callback=null){
    // 忽略脚本超时
    set_time_limit(0);

    // 创建socket
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die('socket创建失败'.socket_strerror(socket_last_error())."\n");
    // 连接服务器
    socket_connect($socket, $host, $port) or die('socket连接失败'.socket_strerror(socket_last_error())."\n");

    // 写入数据
    socket_write($socket, $request, strlen($request)) or die('写入数据失败'.socket_strerror(socket_last_error())."\n");
    // 读取数据
    $response = '';
    while($buffer=socket_read($socket, 1024)){
        $response .= $buffer;
    }
    $response = trim($response);
    // 回调函数
    if(is_callable($callback)){
        call_user_func($callback, $response);
    }else{
        echo $response;
    }

    // 关闭socket
    socket_close($socket);
}
?>