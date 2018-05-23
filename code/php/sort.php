<?php
/**
 * 排序算法
 * @author lilei
 */
class Sort
{
    /**
     * 冒泡排序
     * @param array $array 排序数组
     */
    public static function bubble(&$array)
    {
        for ($i = 1; $i < count($array); $i++) {
            for ($j = 0; $j < count($array) - $i; $j++) {
                if ($array[$j] > $array[$j + 1]) {
                    $temp = $array[$j];
                    $array[$j] = $array[$j + 1];
                    $array[$j + 1] = $temp;
                }
            }
        }
    }

    /**
     * 交换排序
     * @param array $array 排序数组
     */
    public static function exchange(&$array)
    {
        for ($i = 0; $i < count($array) - 1; $i++) {
            for ($j = $i + 1; $j < count($array); $j++) {
                if ($array[$j] < $array[$i]) {
                    $temp = $array[$i];
                    $array[$i] = $array[$j];
                    $array[$j] = $temp;
                }
            }
        }
    }

    /**
     * 选择排序
     * @param array $array 排序数组
     */
    public static function select(&$array)
    {
        for ($i = 0; $i < count($array) - 1; $i++) {
            $index = $i;
            for ($j = $i + 1; $j < count($array); $j++) {
                if ($array[$j] < $array[$index]) {
                    $index = $j;
                }
            }
            $temp = $array[$i];
            $array[$i] = $array[$index];
            $array[$index] = $temp;
        }
    }

    /**
     * 插入排序
     * @param array $array 排序数组
     */
    public static function insert(&$array)
    {
        for ($i = 1; $i < count($array); $i++) {
            $temp = $array[$i];
            $index = $i - 1;
            while ($index >= 0 && $array[$index] > $temp) {
                $array[$index + 1] = $array[$index];
                $index--;
            }
            $array[$index + 1] = $temp;
        }
    }

    /**
     * 快速排序
     * @param array $array 排序数组
     * @param int $min 数组索引最小值
     * @param int $max 数组索引最大值
     */
    public static function quick(&$array, $min, $max)
    {
        $i = $min;
        $j = $max;
        $middle = $array[($min + $max) / 2];
        do {
            while ($array[$i] < $middle && $i < $max) {
                $i++;
            }
            while ($array[$j] > $middle && $j > $min) {
                $j--;
            }
            if ($i <= $j) {
                $temp = $array[$i];
                $array[$i] = $array[$j];
                $array[$j] = $temp;
                $i++;
                $j--;
            }
        } while ($i <= $j);

        if ($j > $min) {
            self::quick($array, $min, $j);
        }
        if ($i < $max) {
            self::quick($array, $i, $max);
        }
    }

    /**
     * 二分法查找
     * @param mixed $find 查找元素
     * @param array $array 排序数组
     * @param int $left 数组索引最小值
     * @param int $right 数组索引最大值
     * @return mixed 找到返回值，找不到返回false
     */
    public static function binaryFind($find, $array, $left, $right)
    {
        if ($left > $right) {
            return false;
        }
        $middle = (int)(($left + $right) / 2);
        if ($find < $array[$middle]) {
            return self::binaryFind($find, $array, $left, $middle - 1);
        } else if ($find > $array[$middle]) {
            return self::binaryFind($find, $array, $middle + 1, $right);
        } else {
            return $middle;
        }
    }
}
?>