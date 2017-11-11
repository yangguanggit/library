<?php
/**
 * mysql数据库操作类
 * @author lilei
 * @property object $self 对象实例
 * @property string $type 数据库类型
 * @property string $host 主机域名／ip
 * @property string $database 数据库名
 * @property string $username 数据库用户名
 * @property string $password 数据库密码
 */
class Db extends PDO{
    protected static $self;
    protected $type;
    protected $host;
    protected $database;
    protected $username;
    protected $password;

    /**
     * 初始化
     * @param string $database 数据库名
     * @param string $username 数据库用户名
     * @param string $password 数据库密码
     * @param string $type 数据库类型
     * @param string $host 主机域名／ip
     * @return object 对象实例
     */
    public static function init($database='mysql', $username='root', $password='root', $type='mysql', $host='127.0.0.1'){
        if(self::$self === null){
            self::$self = new self($type, $host, $database, $username, $password);
        }
        return self::$self;
    }

    /**
     * 构造函数
     * @param string $type 数据库类型
     * @param string $host 主机域名／ip
     * @param string $database 数据库名
     * @param string $username 数据库用户名
     * @param string $password 数据库密码
     * @return null
     */
    public function __construct($type, $host, $database, $username, $password){
        try{
            // 建立连接
            parent::__construct("$type:host=$host;dbname=$database", $username, $password);
            $this->type = $type;
            $this->host = $host;
            $this->database = $database;
            $this->username = $username;
            $this->password = $password;
            // 设置错误模式
            $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // 设置编码
            $this->query('set names utf8');
        }catch(PDOException $e){
            die('数据库连接失败<br>'.$e->getMessage());
        }
    }

    /**
     * 执行insert、update、delete操作
     * @param string $sql sql语句
     * @param array $params 绑定参数
     * @return int insert返回最后插入记录id，update、delete返回受影响记录数
     */
    public function execute($sql, $params=array()){
        try{
            $query = $this->prepare($sql);
            $query->execute($params);
            if(preg_match('/^insert/i', $sql)){
                return $this->lastInsertId();
            }else{
                return $query->rowCount();
            }
        }catch(PDOException $e){
            die('操作失败：'.$this->getSql($sql, $params).'<br>'.$e->getMessage());
        }
    }

    /**
     * 执行select操作
     * @param string $sql sql语句
     * @param array $params 绑定参数
     * @param int $fetch 返回数据格式
     * @return array 一维数组单条查询结果
     */
    public function queryRow($sql, $params=array(), $fetch=PDO::FETCH_ASSOC){
        try{
            $sql = trim(preg_replace('/limit[\s\d]+(?:,[\s\d]+)?/i', '', $sql)).' limit 1';
            $query = $this->prepare($sql);
            $query->execute($params);
            $row = $query->fetch($fetch);
            if($row === false){
                $row = array();
            }
            return $row;
        }catch(PDOException $e){
            die('查询失败：'.$this->getSql($sql, $params).'<br>'.$e->getMessage());
        }
    }

    /**
     * 执行select操作
     * @param string $sql sql语句
     * @param array $params 绑定参数
     * @param int $fetch 返回数据格式
     * @return array 二维数组多条查询结果
     */
    public function queryAll($sql, $params=array(), $fetch=PDO::FETCH_ASSOC){
        try{
            $query = $this->prepare($sql);
            $query->execute($params);
            $rows = $query->fetchAll($fetch);
            return $rows;
        }catch(PDOException $e){
            die('查询失败：'.$this->getSql($sql, $params).'<br>'.$e->getMessage());
        }
    }

    /**
     * 查询记录数
     * @param string $sql sql语句
     * @param array $params 绑定参数
     * @return int 查询记录数
     */
    public function count($sql, $params=array()){
        $sql = preg_replace('/^select.+from/i', 'select count(*) as count from', $sql);
        $count = $this->queryRow($sql, $params);
        return $count['count'];
    }

    /**
     * 分页
     * @param string $sql sql语句
     * @param int $page 当前页码
     * @param int $pageSize 每页显示记录数
     * @param array $params 回带参数
     * @return array 包含数据和页码的数组
     */
    public function page($sql, $page=1, $pageSize=10, $params=array()){
        // 记录数
        $count = $this->count($sql);
        // 页数
        $pageCount = ceil($count/$pageSize);
        $page = (int)$page;
        if($page < 1){
            $page = 1;
        }
        if($pageCount < 1){
            $pageCount = 1;
        }
        if($page > $pageCount){
            $page = $pageCount;
        }

        // 数据
        $data = array();
        $limit = $pageSize*($page-1).','.$pageSize;
        $sql .= " limit $limit";
        $data['data'] = $this->queryAll($sql);

        // 页码
        if($pageCount > 10){
            if($page < 10){
                $start = 1;
                $stop = 10;
            }else if($page > $pageCount-10){
                $start = $pageCount-10;
                $stop = $pageCount;
            }else{
                $start = $page;
                $stop = $page+10;
            }
        }else{
            $start = 1;
            $stop = $pageCount;
        }
        $html = '<div class="page" style="margin:0 5%;width:90%;height:100px;line-height:100px;text-align:center;color:black;">';
        if($page > 1){
            $html .= '<a class="page_number" href="'.$_SERVER['SCRIPT_NAME'].'?page=1&'.http_build_query($params).'" style="color:black;text-decoration:none;"><span style="margin-right:10px;padding:5px 10px;border:1px solid #ccc;">首页</span></a>';
        }
        for($i=$start; $i<=$stop; $i++){
            if($i == $page){
                $html .= '<span style="margin-right:10px;padding:5px 10px;background-color:#f57c00;border:1px solid #f57c00;color:white;">'.$i.'</span>';
            }else{
                $html .= '<a class="page_number" href="'.$_SERVER['SCRIPT_NAME'].'?page='.$i.'&'.http_build_query($params).'" style="color:black;text-decoration:none;"><span style="margin-right:10px;padding:5px 10px;border:1px solid #ccc;">'.$i.'</span></a>';
            }
        }
        if($page < $pageCount){
            $html .= '<a class="page_number" href="'.$_SERVER['SCRIPT_NAME'].'?page='.$pageCount.'&'.http_build_query($params).'" style="color:black;text-decoration:none;"><span style="margin-right:10px;padding:5px 10px;border:1px solid #ccc;">末页</span></a>';
        }
        $html .= '<span style="margin-right:10px;padding:5px 10px;border:none;">第'.$page.'页／共'.$pageCount.'页</span>';
        $html .= '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="get" style="display:inline;"><input class="page_text" type="text" name="page" value="'.$page.'" style="padding:5px 10px;width:50px;border:1px solid #f57c00;border-right:none;" />';
        if($params){
            foreach($params as $k => $v){
                $html .= '<input type="hidden" name="'.$k.'" value="'.$v.'" />';
            }
        }
        $html .= '<input class="page_submit" type="submit" value="跳转" style="margin-left:-5px;padding:5px 10px;width:50px;background-color:#f57c00;border:1px solid #f57c00;color:white;" /></form>';
        $html .= '</div>';
        $data['page'] = $html;
        
        return $data;
    }

    /**
     * 获取数据库名
     * @param null
     * @return string 数据库名
     */
    public function getDatabase(){
        return $this->database;
    }

    /**
     * 获取sql
     * @param string $sql sql语句
     * @param array $params 绑定参数
     * @return string 绑定参数填充后的sql语句
     */
    public function getSql($sql, $params=array()){
        if(empty($params)){
            return $sql;
        }
        if(preg_match('/:\w+/', $sql)){
            // 字符串占位符
            $sql = strtr($sql, $params);
            return preg_replace('/:\w+/', '', $sql);
        }else if(preg_match('/\?/', $sql)){
            // ?占位符
            $array = explode('?', $sql);
            $sql = '';
            foreach($array as $k => $v){
                $sql .= $v.(isset($params[$k]) ? $params[$k] : '');
            }
            return $sql;
        }
    }

    /**
     * 数据过滤
     * @param int|string|array $value 输入值
     * @return int|string|array 数字不变，字符串加单引号，特殊字符转义
     */
    public function filter($value){
        if(is_array($value)){
            foreach($value as $k => $v){
                if(is_numeric($v)){
                    continue;
                }
                if(get_magic_quotes_gpc()){
                    $v = stripslashes($v);
                }
                $v = trim($v);
                $v = $this->quote($v);
                $v = htmlspecialchars($v);
                $value[$k] = $v;
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
            $value = $this->quote($value);
            $value = htmlspecialchars($value);
            return $value;
        }
    }

    /**
     * 正则校验
     * @param int|string $value 输入值
     * @param string $type 类型
     * @return bool|array 数组包含匹配到的信息
     */
    public function check($value, $type){
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
}
?>