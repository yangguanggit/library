<?php
/**
 * 文件上传类
 * @author lilei
 */
class FileUpload
{
    protected static $that;
    protected $file;
    protected $path;
    protected $url;
    protected $ext = array(
        'image' => array('png', 'jpg', 'gif', 'jpeg'),
        'document' => array('txt', 'doc', 'xls', 'ppt', 'pdf', 'docx', 'xlsx', 'pptx'),
        'compress' => array('zip', 'rar')
    );

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
     * 转换文件数组格式（多文件上传）
     * @return $this 对象实例
     */
    protected function exchange()
    {
        $array = array();
        foreach ($this->file as $k => $v) {
            foreach ($v as $i => $j) {
                $array[$i][$k] = $j;
            }
        }
        $this->file = $array;
        return $this;
    }

    /**
     * 判断文件是否上传
     * @param string $name 文件名
     * @return bool
     */
    protected function isUpload($name)
    {
        return is_uploaded_file($name);
    }

    /**
     * 检查文件大小
     * @param int $size 文件大小
     * @param int $max 最大上传文件大小
     * @return bool
     */
    protected function checkSize($size, $max = 2)
    {
        return $size != 0 && $size < $max * 1024 * 1024;
    }

    /**
     * 检查文件格式
     * @param string $name 文件名
     * @param string $type 文件类型
     * @return bool|string 成功返回文件类型，失败返回false
     */
    protected function checkExtension($name, $type)
    {
        $info = pathinfo($name);
        $ext = strtolower($info['extension']);
        if ($type == 'all') {
            if (in_array($ext, $this->ext['image'])) {
                $type = 'image';
            } else if (in_array($ext, $this->ext['document'])) {
                $type = 'document';
            } else if (in_array($ext, $this->ext['compress'])) {
                $type = 'compress';
            } else {
                return false;
            }
        } else if ($type == 'image' && !in_array($ext, $this->ext['image'])) {
            return false;
        } else if ($type == 'document' && !in_array($ext, $this->ext['document'])) {
            return false;
        } else if ($type == 'compress' && !in_array($ext, $this->ext['compress'])) {
            return false;
        }
        return $type;
    }

    /**
     * 创建目录
     * @param string $dir 文件保存子目录
     * @return $this 对象实例
     */
    protected function makeDir($dir = '')
    {
        $this->path = $_SERVER['DOCUMENT_ROOT'] . '/upload';
        $this->url = '/upload';
        if ($dir) {
            $this->path .= '/' . $dir;
            $this->url .= '/' . $dir;
        }
        if (!is_dir($this->path)) {
            mkdir($this->path, 0777, true);
            chmod($this->path, 0777);
        }
        return $this;
    }

    /**
     * 生成新的文件名
     * @param string $name 原文件名
     * @return $this 对象实例
     */
    protected function generateName($name = '')
    {
        $name = date('YmdHis') . mt_rand(10, 99) . $name;
        $this->path .= '/' . $name;
        $this->url .= '/' . $name;
        return $this;
    }

    /**
     * 移动临时文件到上传文件保存目录
     * @param string $name 临时文件名
     * @return bool
     */
    protected function moveUpload($name)
    {
        return move_uploaded_file($name, $this->path);
    }

    /**
     * 单文件上传
     * @param string $name 表单文件控件名
     * @param string $type 文件类型
     * @param int $max 最大上传文件大小（单位M）
     * @param string $dir 文件保存目录
     * @return array|string 成功返回数组，失败返回错误信息
     */
    public function upload($name, $type = 'all', $max = 2, $dir = '')
    {
        if (empty($_FILES[$name])) {
            return '数据错误';
        }
        $this->file = $_FILES[$name];
        $error = $this->file['error'];
        $tempName = $this->file['tmp_name'];
        $name = $this->file['name'];
        $size = $this->file['size'];
        if ($this->isUpload($tempName) && $this->checkSize($size, $max) && ($fileType = $this->checkExtension($name, $type))) {
            if ($this->makeDir($dir)->generateName($name)->moveUpload($tempName)) {
                return array('name' => $name, 'type' => $fileType, 'url' => $this->url);
            }
        }
        return $error;
    }

    /**
     * 多文件上传
     * @param string $name 表单文件控件名
     * @param string $type 文件类型
     * @param int $max 最大上传文件大小（单位M）
     * @param string $dir 文件保存目录
     * @return array|string 成功返回文件信息数组，错误返回提示信息
     */
    public function multipleUpload($name, $type = 'all', $max = 2, $dir = '')
    {
        if (empty($_FILES[$name])) {
            return '数据错误';
        }
        $this->file = $_FILES[$name];
        $this->exchange();
        $result = array();
        $success = count($this->file);
        $fail = 0;
        foreach ($this->file as $v) {
            $tempName = $v['tmp_name'];
            $name = $v['name'];
            $size = $v['size'];
            if ($this->isUpload($tempName) && $this->checkSize($size, $max) && ($fileType = $this->checkExtension($name, $type))) {
                if ($this->makeDir($dir)->generateName($name)->moveUpload($tempName)) {
                    $result['data'][] = array('name' => $name, 'type' => $fileType, 'url' => $this->url);
                    continue;
                }
            }
            $success--;
            $fail++;
        }

        $result['success'] = $success;
        $result['fail'] = $fail;
        return $result;
    }
}
?>