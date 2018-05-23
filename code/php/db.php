<?php
/**
 * 数据库操作类
 * @author lilei
 */
class Db extends PDO
{
    protected static $that = array();
    protected $database;
    protected static $config = array(
        'type' => 'mysql',
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'mysql',
        'username' => 'root',
        'password' => 'root'
    );

    /**
     * 初始化
     * @param array $config 配置数组
     * @return $this 对象实例
     */
    public static function init($config = array())
    {
        if ($config) {
            self::$config = $config + self::$config;
        }
        extract(self::$config);
        if (empty(self::$that[$database]) || !(self::$that[$database] instanceof self)) {
            $dsn = "$type:host=$host;port=$port;dbname=$database;charset=utf8";
            self::$that[$database] = new self($dsn, $username, $password);
        }
        return self::$that[$database];
    }

    /**
     * 构造函数，由于继承PDO不能私有化，请通过init()方法获取对象实例
     * @param string $dsn 连接字符串
     * @param string $username 数据库用户名
     * @param string $password 数据库密码
     */
    public function __construct($dsn, $username, $password)
    {
        try {
            // 建立连接
            parent::__construct($dsn, $username, $password);
            // 设置错误模式
            $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // 保存数据库名称
            $this->database = self::$config['database'];
        } catch (PDOException $e) {
            die('数据库连接失败<br>' . $e->getMessage());
        }
    }

    /**
     * 禁止克隆
     */
    protected function __clone()
    {

    }

    /**
     * 执行insert、update、delete操作
     * @param string $sql sql语句
     * @param array $params 绑定参数
     * @return int insert返回最后插入记录id，update、delete返回受影响记录数
     */
    public function execute($sql, $params = array())
    {
        try {
            $query = $this->prepare($sql);
            $query->execute($params);
            if (preg_match('/^insert/i', $sql)) {
                return $this->lastInsertId();
            } else {
                return $query->rowCount();
            }
        } catch (PDOException $e) {
            die('操作失败：' . $this->getSql($sql, $params) . '<br>' . $e->getMessage());
        }
    }

    /**
     * 执行select操作
     * @param string $sql sql语句
     * @param array $params 绑定参数
     * @param int $fetch 返回数据格式
     * @return array 一维数组单条查询结果
     */
    public function queryOne($sql, $params = array(), $fetch = PDO::FETCH_ASSOC)
    {
        try {
            $query = $this->prepare($sql);
            $query->execute($params);
            $row = $query->fetch($fetch);
            if ($row === false) {
                $row = array();
            }
            return $row;
        } catch (PDOException $e) {
            die('查询失败：' . $this->getSql($sql, $params) . '<br>' . $e->getMessage());
        }
    }

    /**
     * 执行select操作
     * @param string $sql sql语句
     * @param array $params 绑定参数
     * @param int $fetch 返回数据格式
     * @return array 二维数组多条查询结果
     */
    public function queryAll($sql, $params = array(), $fetch = PDO::FETCH_ASSOC)
    {
        try {
            $query = $this->prepare($sql);
            $query->execute($params);
            return $query->fetchAll($fetch);
        } catch (PDOException $e) {
            die('查询失败：' . $this->getSql($sql, $params) . '<br>' . $e->getMessage());
        }
    }

    /**
     * 获取sql
     * @param string $sql sql语句
     * @param array $params 绑定参数
     * @return string 绑定参数填充后的sql语句
     */
    public function getSql($sql, $params = array())
    {
        if (empty($params)) {
            return $sql;
        }
        if (preg_match('/:\w+/', $sql)) {
            // 字符串占位符
            $sql = strtr($sql, $params);
            return preg_replace('/:\w+/', '', $sql);
        } else if (preg_match('/\?/', $sql)) {
            // ?占位符
            $array = explode('?', $sql);
            $sql = '';
            foreach ($array as $k => $v) {
                $sql .= $v . (isset($params[$k]) ? $params[$k] : '');
            }
        }
        return $sql;
    }

    /**
     * 获取数据库名
     * @return string 数据库名
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * 数据过滤
     * @param int|string|array $value 输入值
     * @return int|string|array 数字不变，字符串加单引号，特殊字符转义
     */
    public function filter($value)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                if (is_numeric($v)) {
                    continue;
                }
                if (get_magic_quotes_gpc()) {
                    $v = stripslashes($v);
                }
                $v = trim($v);
                $v = $this->quote($v);
                $v = htmlspecialchars($v);
                $value[$k] = $v;
            }
            return $value;
        } else {
            if (is_numeric($value)) {
                return $value;
            }
            if (get_magic_quotes_gpc()) {
                $value = stripslashes($value);
            }
            $value = trim($value);
            $value = $this->quote($value);
            $value = htmlspecialchars($value);
            return $value;
        }
    }
}
?>