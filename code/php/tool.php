<?php
/**
 * 工具类
 * @author lilei
 */
class Tool
{
    /**
     * 文件日志
     * @param string|array|object $data 日志数据
     * @param string $name 日志文件名
     */
    public static function fileLog($data, $name)
    {
        $path = $_SERVER['DOCUMENT_ROOT'] . '/log';
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
            chmod($path, 0777);
        }
        $file = fopen($path . '/' . $name, 'a');
        fwrite($file, date('Y-m-d H:i:s') . PHP_EOL);
        if (is_array($data) || is_object($data)) {
            foreach ($data as $k => $v) {
                fwrite($file, $k . '=' . $v . ';');
            }
        } else {
            fwrite($file, $data);
        }
        fwrite($file, PHP_EOL . PHP_EOL);
        fclose($file);
    }

    /**
     * 读取文件夹下文件列表
     * @param string $path 文件夹路径
     * @return array 文件夹下文件列表
     */
    public static function readDirectory($path)
    {
        $files = array();
        if ($directory = opendir($path)) {
            while ($file = readdir($directory)) {
                if ($file != '.' && $file != '..' && $file != '.DS_Store') {
                    $files[] = $path . '/' . $file;
                }
            }
            closedir($directory);
        }
        return $files;
    }

    /**
     * 数组转对象
     * @param array $array 数组
     * @return StdClass 对象
     */
    public static function arrayToObject($array)
    {
        if (is_array($array)) {
            $object = new StdClass();
            foreach ($array as $k => $v) {
                $object->$k = $v;
            }
        } else {
            $object = $array;
        }
        return $object;
    }

    /**
     * 对象转数组
     * @param object $object 对象
     * @return array 数组
     */
    public static function objectToArray($object)
    {
        if (is_object($object)) {
            $array = array();
            foreach ($object as $k => $v) {
                $array[$k] = $v;
            }
        } else {
            $array = $object;
        }
        return $array;
    }

    /**
     * 多维数组转一维数组
     * @param array $array 多维数组
     * @return array 一维数组
     */
    public static function oneDimensional($array)
    {
        $result = array();
        array_walk_recursive($array, function ($v, $k) use (&$result) {
            if (is_numeric($k)) {
                $result[] = $v;
            } else {
                $result[$k] = $v;
            }
        });
        return $result;
    }

    /**
     * 解析xml
     * @param string $xml xml字符串
     * @return array|false 成功返回数组格式数据，失败返回false
     */
    public static function parseXml($xml)
    {
        $parser = xml_parser_create();
        if (xml_parse($parser, $xml, true)) {
            return json_decode(json_encode(simplexml_load_string($xml)), true);
        } else {
            xml_parser_free($parser);
            return false;
        }
    }

    /**
     * 计算时间差
     * @param int $time 指定时间戳
     * @return string 时间差
     */
    public static function timeDiff($time)
    {
        $time = time() - $time;
        $text = $time > 0 ? '前' : '后';
        $time = abs($time);
        $unit = array(
            '31536000' => '年',
            '2592000' => '个月',
            '604800' => '周',
            '86400' => '天',
            '3600' => '小时',
            '60' => '分钟',
            '1' => '秒'
        );
        foreach ($unit as $k => $v) {
            $result = floor($time / (int)$k);
            if ($result != 0) {
                return $result . $v . $text;
            }
        }
    }

    /**
     * file_get_contents模拟post请求
     * @param string $url 请求地址（完整url）
     * @param array $params 数据
     * @return string 响应数据
     */
    public static function filePost($url, $params = array())
    {
        $option = array(
            'http' => array(
                'method' => 'POST',
                'header' => "Content-Type:application/x-www-form-urlencoded",
                'content' => http_build_query($params)
            )
        );
        $response = file_get_contents($url, false, stream_context_create($option));
        return trim($response);
    }

    /**
     * curl模拟post请求
     * @param string $url 请求地址（完整url）
     * @param array $params 数据
     * @return string 响应数据
     */
    public static function curlPost($url, $params = array())
    {
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
     * @param string $url 请求地址（完整url）
     * @param array $params 数据
     * @return string 响应数据
     */
    public static function sockPost($url, $params = array())
    {
        $response = '';
        $parse = parse_url($url);
        // $scheme = $parse['scheme'];
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
        $header .= 'Content-Length: ' . strlen($params) . "\r\n";
        $header .= "\r\n";
        $header .= $params;
        fputs($file, $header);
        // http响应头（读到头部和响应数据之间换行停止）
        $header = '';
        while ($buffer = trim(fgets($file, 1024))) {
            $header .= $buffer;
        }
        // 响应数据
        while (!feof($file)) {
            $response .= fgets($file, 1024);
        }
        // 关闭连接
        fclose($file);
        return trim($response);
    }

    /**
     * 命令行获取用户输入
     * @param string $text 提示信息
     * @return string 用户输入
     */
    public static function cliInput($text)
    {
        // 提示用户输入
        fputs(STDOUT, $text);
        // 获取用户输入
        $input = trim(fgets(STDIN));
        return $input;
    }
}
?>