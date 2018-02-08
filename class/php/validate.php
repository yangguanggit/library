<?php
/**
 * 校验类
 * @author lilei
 */
class Validate
{
    protected static $that;

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
     * 正则校验
     * @param int|string $value 输入值
     * @param string $type 类型
     * @return bool|array 数组包含匹配到的信息
     */
    public function check($value, $type)
    {
        switch ($type) {
            case 'id':
                // 身份证号码
                if (preg_match('/^([1-9]\d{5})(\d{4})(\d{2})(\d{2})\d{3}[\dX]$/', $value, $match)) {
                    return $match;
                } else {
                    return false;
                }
                break;
            case 'mobile':
                // 手机号码
                if (preg_match('/^1[345678]\d{9}$/', $value)) {
                    return true;
                } else {
                    return false;
                }
                break;
            case 'phone':
                // 电话号码
                if (preg_match('/^\d{3}-\d{8}|\d{4}-\d{7,8}$/', $value)) {
                    return true;
                } else {
                    return false;
                }
                break;
            case 'qq':
                // qq号码
                if (preg_match('/^[1-9]\d{4,11}$/', $value)) {
                    return true;
                } else {
                    return false;
                }
                break;
            case 'email':
                // 邮箱
                if (preg_match('/^\w+(?:[.-]?\w+)*@\w+(?:[.-]?\w+)*\.[a-zA-Z]+$/', $value)) {
                    return true;
                } else {
                    return false;
                }
                break;
            case 'url':
                // 网址
                if (preg_match('/^(https?):\/\/([^\s\/:]+)(:\d+)?(\S*)$/', $value, $match)) {
                    return $match;
                } else {
                    return false;
                }
                break;
            case 'html':
                // html标签
                if (preg_match('/^<(\w+)[^>]*>.*?<\/\1>$|^<\w+[^>]*>$/', $value)) {
                    return true;
                } else {
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