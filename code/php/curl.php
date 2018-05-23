<?php
/**
 * curl操作类
 * @author lilei
 */
class Curl
{
    protected static $that;
    protected $curl;
    protected $config = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HEADER => false,
        CURLOPT_VERBOSE => true,
        CURLOPT_AUTOREFERER => true,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'
    );
    protected $response;

    /**
     * 初始化
     * @param array $config 配置数组
     * @return $this 对象实例
     */
    public static function init($config = array())
    {
        if (empty(self::$that) || !(self::$that instanceof self)) {
            self::$that = new self($config);
        }
        return self::$that;
    }

    /**
     * 构造函数
     * @param array $config 配置数组
     */
    protected function __construct($config = array())
    {
        try {
            if ($config) {
                $this->config = $config + $this->config;
            }
            $this->curl = curl_init();
            curl_setopt_array($this->curl, $this->config);
        } catch (Exception $e) {
            die('请安装curl');
        }
    }

    /**
     * 禁止克隆
     */
    protected function __clone()
    {

    }

    /**
     * 执行
     * @param string $url 请求地址
     * @return string 请求成功返回响应数据，请求失败返回错误信息
     */
    protected function execute($url)
    {
        curl_setopt($this->curl, CURLOPT_URL, $url);
        $this->response = curl_exec($this->curl);
        if (curl_errno($this->curl)) {
            return curl_error($this->curl);
        } else {
            if ($this->config[CURLOPT_HEADER]) {
                $headerSize = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
                return substr(trim($this->response), $headerSize);
            }
            return trim($this->response);
        }
    }

    /**
     * get请求
     * @param string $url 请求地址
     * @param array $params 请求参数
     * @return string 响应数据
     */
    public function get($url, $params = array())
    {
        curl_setopt($this->curl, CURLOPT_HTTPGET, true);
        return $this->execute($this->buildUrl($url, $params));
    }

    /**
     * post请求
     * @param string $url 请求地址
     * @param array $params 请求参数
     * @return string 响应数据
     */
    public function post($url, $params = array())
    {
        curl_setopt($this->curl, CURLOPT_POST, true);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($params));
        return $this->execute($url);
    }

    /**
     * delete请求
     * @param string $url 请求地址
     * @param array $params 请求参数
     * @return string 响应数据
     */
    public function delete($url, $params = array())
    {
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
        return $this->execute($this->buildUrl($url, $params));
    }

    /**
     * put请求
     * @param string $url 请求地址
     * @param string $data 请求数据
     * @param array $params 请求参数
     * @return string 响应数据
     */
    public function put($url, $data = '', $params = array())
    {
        // 写入内存或缓存
        $fp = fopen('php://temp', 'rw+');
        fwrite($fp, $data);
        rewind($fp);

        curl_setopt($this->curl, CURLOPT_PUT, true);
        curl_setopt($this->curl, CURLOPT_INFILE, $fp);
        curl_setopt($this->curl, CURLOPT_INFILESIZE, strlen($data));
        return $this->execute($this->buildUrl($url, $params));
    }

    /**
     * 设置header选项
     * @param array $header 头部选项
     * @return $this 对象实例
     */
    public function setHeader($header = array())
    {
        if ($this->isAssoc($header)) {
            $out = array();
            foreach ($header as $k => $v) {
                $out[] = $k . ': ' . $v;
            }
            $header = $out;
        }
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $header);
        return $this;
    }

    /**
     * 获取header选项
     * @return array 头部选项
     */
    public function getHeader()
    {
        $header = array();
        $headerString = substr($this->response, 0, strpos($this->response, "\r\n\r\n"));
        foreach (explode("\r\n", $headerString) as $k => $v) {
            if ($k === 0) {
                $header['http_code'] = $v;
            } else {
                list($key, $value) = explode(': ', $v);
                $header[$key] = $value;
            }
        }
        return $header;
    }

    /**
     * 获取curl信息
     * @return array curl信息
     */
    public function getInfo()
    {
        return curl_getinfo($this->curl);
    }

    /**
     * 获取错误信息
     * @return string 错误信息
     */
    public function getError()
    {
        return curl_error($this->curl);
    }

    /**
     * 组合请求地址
     * @param string $url 请求地址
     * @param array $params 请求参数
     * @return string 组合请求地址
     */
    public function buildUrl($url, $params = array())
    {
        $parse = parse_url($url);
        $parse['port'] = isset($parse['port']) ? ':' . $parse['port'] : '';
        $parse['path'] = isset($parse['path']) ? $parse['path'] : '/';
        isset($parse['query']) ? parse_str($parse['query'], $parse['query']) : $parse['query'] = array();
        $params = array_merge($parse['query'], $params);
        $parse['query'] = $params ? '?' . http_build_query($params) : '';
        return $parse['scheme'] . '://' . $parse['host'] . $parse['port'] . $parse['path'] . $parse['query'];
    }

    /**
     * 判断是否关联数组
     * @param array $array 数组
     * @return bool
     */
    public function isAssoc($array)
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }
}
?>