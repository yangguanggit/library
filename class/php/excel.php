<?php
/**
 * 导出excel表格
 * @author lilei
 * @note 设置导出表格单元格类型（不设置浏览器自动识别）
 * @option 文本 vnd.ms-excel.numberformat:@
 * @option 数字 vnd.ms-excel.numberformat:#,##0.00
 * @option 日期 vnd.ms-excel.numberformat:yyyy/mm/dd
 * @option 百分比 vnd.ms-excel.numberformat: #0.00%
 * @option 货币 vnd.ms-excel.numberformat:￥#,##0.00
 * @example <td style="vnd.ms-excel.numberformat:@">0123456789</td>
 */
// 表格数据
$data = array(
    array('name' => '张三', 'sex' => '男', 'age' => '20', 'address' => '北京'),
    array('name' => '李四', 'sex' => '男', 'age' => '20', 'address' => '上海'),
    array('name' => '王五', 'sex' => '男', 'age' => '20', 'address' => '广州')
);
// 文件名
$name = date('YmdHis') . '.xls';

// 禁止缓存
header('Cache-Control:no-cache,must-revalidate');
header('Pragma:no-cache');
header('Expires:0');
// 下载文件
header('Content-Type:application/vnd.ms-excel');
header("Content-Disposition:attachment;filename=$name");
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
    <title>导出excel表格</title>
</head>
<body>
    <table width="100%" border="1" cellspacing="1" cellpadding="1" align="center">
        <!-- 表头 -->
        <thead>
            <tr style="font-size:16px;text-align:center;">
                <th width="20%">序号</th>
                <th width="20%">姓名</th>
                <th width="20%">性别</th>
                <th width="20%">年龄</th>
                <th width="20%">地址</th>
            </tr>
        </thead>
        <!-- 数据 -->
        <tbody>
            <?php for ($i = 0; $i < count($data); $i++) { ?>
                <tr style="font-size:14px;text-align:center;">
                    <td><?php echo $i + 1; ?></td>
                    <td><?php echo $data[$i]['name']; ?></td>
                    <td><?php echo $data[$i]['sex']; ?></td>
                    <td><?php echo $data[$i]['age']; ?></td>
                    <td><?php echo $data[$i]['address']; ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</body>
</html>