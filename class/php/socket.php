<?php
/**
 * socket操作类
 * @author lilei
 */
class Socket
{
    protected static $that;
    protected $socket;

    /**
     * 初始化
     * @return $this 对象实例
     */
    public static function init()
    {
        if (empty(self::$that) || !(self::$that instanceof self)) {
            self::$that = new self();
        }
        return self::$that;
    }

    /**
     * 构造函数
     */
    protected function __construct()
    {

    }

    /**
     * 禁止克隆
     */
    protected function __clone()
    {

    }

    /**
     * 创建socket
     * @return $this 对象实例
     */
    protected function create()
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die('socket创建失败' . socket_strerror(socket_last_error()) . "\n");
        return $this;
    }

    /**
     * 服务器绑定
     * @param string $host 地址
     * @param int $port 端口
     * @return $this 对象实例
     */
    protected function bind($host, $port)
    {
        socket_bind($this->socket, $host, $port) or die('socket绑定失败' . socket_strerror(socket_last_error()) . "\n");
        return $this;
    }

    /**
     * 服务器监听
     * @param int $backlog 阻塞队列长度
     * @return $this 对象实例
     */
    protected function listen($backlog = 100)
    {
        socket_listen($this->socket, $backlog) or die('socket监听失败' . socket_strerror(socket_last_error()) . "\n");
        return $this;
    }

    /**
     * 服务器接受请求
     * @return resource|bool 成功返回文件描述符，失败返回false
     */
    protected function accept()
    {
        return socket_accept($this->socket) or die('接受请求失败' . socket_strerror(socket_last_error()) . "\n");
    }

    /**
     * 读数据
     * @param resource $fd socket描述符
     * @param int $length 一次读取数据长度
     * @return string|bool 成功返回数据，失败返回false
     */
    protected function read($fd = null, $length = 1024)
    {
        $fd = $fd ?: $this->socket;
        return socket_read($fd, $length) or die('读取数据失败' . socket_strerror(socket_last_error()) . "\n");
    }

    /**
     * 写数据
     * @param resource $fd socket描述符
     * @param string $data 数据
     * @return int|bool 成功返回数据字节数，失败返回false
     */
    protected function write($fd = null, $data = '')
    {
        $fd = $fd ?: $this->socket;
        return socket_write($fd, $data, strlen($data)) or die('写入数据失败' . socket_strerror(socket_last_error()) . "\n");
    }

    /**
     * 客户端连接服务器
     * @param string $host 地址
     * @param int $port 端口
     * @return $this 对象实例
     */
    protected function connect($host, $port)
    {
        socket_connect($this->socket, $host, $port) or die('socket连接失败' . socket_strerror(socket_last_error()) . "\n");
        return $this;
    }

    /**
     * 关闭socket
     * @param resource $socket socket描述符
     */
    protected function close($socket = null)
    {
        $socket = $socket ?: $this->socket;
        socket_close($socket);
    }

    /**
     * socket服务端
     * @param string $host 地址
     * @param int $port 端口
     * @param string|callable $callback 回调函数（请求数据注入参数）
     */
    public function server($host, $port, $callback = null)
    {
        // 忽略脚本超时
        set_time_limit(0);
        // 创建socket、绑定、监听
        $this->create()
            ->bind($host, $port)
            ->listen();
        while (true) {
            // 另一个socket处理通信，阻塞状态，等待接受客户端请求
            $fd = $this->accept();
            // 读取数据
            $request = $this->read($fd);
            $request = trim($request);
            $response = 'success';
            // 回调函数
            if (is_callable($callback)) {
                $response = call_user_func($callback, $request);
            }
            // 写入数据
            $this->write($fd, $response);
            // 关闭socket
            $this->close($fd);
        }
        // 关闭socket
        $this->close();
    }

    /**
     * socket客户端
     * @param string $host 地址
     * @param int $port 端口
     * @param string $request 请求数据
     * @param string|callable $callback 回调函数（响应数据注入参数）
     * @return string|mixed 响应数据
     */
    public function client($host, $port, $request, $callback = null)
    {
        // 忽略脚本超时
        set_time_limit(0);
        // 创建socket、连接、写入数据
        $this->create()
            ->connect($host, $port)
            ->write(null, $request);
        // 读取数据
        $response = '';
        while ($buffer = $this->read()) {
            $response .= $buffer;
        }
        $response = trim($response);
        // 关闭socket
        $this->close();
        // 回调函数
        if (is_callable($callback)) {
            return call_user_func($callback, $response);
        }
        return $response;
    }
}
?>