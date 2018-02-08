<?php
/**
 * 输出数据字典
 * @author lilei
 */
header('Content-Type:text/html;charset=utf-8');
include_once 'db.php';
$db = Db::init();
// 数据库名
$database = $db->getDatabase();

$result = array();
// 查询表名
$tables = $db->queryAll('show tables', array(), PDO::FETCH_NUM);
foreach ($tables as $v) {
    $result[]['TABLE_NAME'] = $v[0];
}
if (empty($result)) {
    die('数据库为空');
}
// 查询表信息
foreach ($result as $k => $v) {
    // 查询表备注
    $sql = "select * from information_schema.tables where table_schema='$database' and table_name='" . $v['TABLE_NAME'] . "'";
    $comments = $db->queryAll($sql);
    foreach ($comments as $c) {
        $result[$k]['TABLE_COMMENT'] = $c['TABLE_COMMENT'];
    }
    // 查询字段信息
    $sql = "select * from information_schema.columns where table_schema='$database' and table_name='" . $v['TABLE_NAME'] . "'";
    $columns = $db->queryAll($sql);
    foreach ($columns as $c) {
        $result[$k]['COLUMN'][] = $c;
    }
}
// 释放资源，关闭连接
$db = null;
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
    <title>数据字典</title>
    <style type="text/css">
        body {
            font-family: Arial, "宋体";
            font-size: 14px;
        }

        table {
            margin-left: 20%;
            width: 60%;
            border: 1px solid #ccc;
            border-collapse: collapse;
        }

        table caption {
            height: 30px;
            line-height: 30px;
            font-weight: bold;
        }

        table th {
            background: linear-gradient(#f1f3f2, #dfe1e0);
            border: 1px solid #ccc;
        }

        table td {
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>
    <h1 align="center">数据字典</h1>
    <?php foreach ($result as $k => $v) { ?>
        <h3><?php echo $v['TABLE_COMMENT']; ?></h3>
        <table border="1" cellspacing="0" cellpadding="0" align="center">
            <caption><?php echo $v['TABLE_NAME']; ?></caption>
            <thead>
                <tr>
                    <th width="20%">字段</th>
                    <th width="10%">类型</th>
                    <th width="10%">主键</th>
                    <th width="10%">自增</th>
                    <th width="10%">默认</th>
                    <th width="10%">null</th>
                    <th width="30%">备注</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($v['COLUMN'] as $c) { ?>
                    <tr>
                        <td><?php echo $c['COLUMN_NAME']; ?></td>
                        <td><?php echo $c['COLUMN_TYPE']; ?></td>
                        <td><?php echo $c['COLUMN_KEY'] == 'PRI' ? 'yes' : 'no'; ?></td>
                        <td><?php echo $c['EXTRA'] == 'auto_increment' ? 'yes' : 'no'; ?></td>
                        <td><?php echo $c['COLUMN_DEFAULT']; ?></td>
                        <td><?php echo strtolower($c['IS_NULLABLE']); ?></td>
                        <td><?php echo $c['COLUMN_COMMENT']; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } ?>
    <script type="text/javascript">
        window.onload = function () {
            var table = document.getElementsByTagName('table');
            for (var i = 0; i < table.length; i++) {
                var tr = table[i].getElementsByTagName('tr');
                for (var j = 0; j < tr.length; j++) {
                    if (j % 2 == 0) {
                        tr[j].style.background = '#f0f0f0';
                    }
                }
            }
        }
    </script>
</body>
</html>