package mysql

import (
	"database/sql"
	_ "github.com/go-sql-driver/mysql"
	"regexp"
)

/**
 * 获取数据库连接
 * @param string dsn 数据库连接字符串"root:root@tcp(127.0.0.1:3306)/mysql?charset=utf8&loc=Local"
 * @return resource, error 数据库连接
 */
func Db(dsn string) (*sql.DB, error) {
	// 初始化连接池
	db, err := sql.Open("mysql", dsn)
	if err != nil {
		return nil, err
	}
	return db, nil
}

/**
 * 执行insert、update、delete操作
 * @param resource db 数据库连接
 * @param string sql sql语句
 * @param mixed args 绑定参数
 * @return int, error insert操作返回id，update、delete操作返回受影响行数
 */
func Execute(db *sql.DB, sql string, args ...interface{}) (int64, error) {
	// 预编译
	stmt, err := db.Prepare(sql)
	if err != nil {
		return 0, err
	}
	result, err := stmt.Exec(args...)
	if err != nil {
		return 0, err
	}
	match, _ := regexp.MatchString("(?i)^insert", sql)
	if match {
		// insert操作返回id
		id, _ := result.LastInsertId()
		return id, nil
	} else {
		// update、delete操作返回受影响行数
		count, _ := result.RowsAffected()
		return count, nil
	}
}

/**
 * 执行select操作
 * @param resource db 数据库连接
 * @param string sql sql语句
 * @param mixed args 绑定参数
 * @return map, error 单条数据
 */
func QueryOne(db *sql.DB, sql string, args ...interface{}) (map[string]interface{}, error) {
	// 预编译
	stmt, err := db.Prepare(sql)
	if err != nil {
		return nil, err
	}
	// 查询
	row, err := stmt.Query(args...)
	if err != nil {
		return nil, err
	}
	// 获取字段名
	columns, _ := row.Columns()
	count := len(columns)
	result := make(map[string]interface{}, count)
	// 定义切片存放数据
	value := make([]string, count)
	// 定义指针切片接收数据
	ptr := make([]interface{}, count)
	// ptr元素指向values元素
	for i := 0; i < count; i++ {
		ptr[i] = &value[i]
	}
	// 读取一条数据
	if row.Next() {
		row.Scan(ptr...)
		for i, column := range columns {
			result[column] = value[i]
		}
	}
	return result, nil
}

/**
 * 执行select操作
 * @param resource db 数据库连接
 * @param string sql sql语句
 * @param mixed args 绑定参数
 * @return slice, error 多条数据
 */
func QueryAll(db *sql.DB, sql string, args ...interface{}) ([]map[string]interface{}, error) {
	// 预编译
	stmt, err := db.Prepare(sql)
	if err != nil {
		return nil, err
	}
	rows, err := stmt.Query(args...)
	if err != nil {
		return nil, err
	}
	columns, _ := rows.Columns()
	count := len(columns)
	var result []map[string]interface{}
	value := make([]string, count)
	ptr := make([]interface{}, count)
	for i := 0; i < count; i++ {
		ptr[i] = &value[i]
	}
	// 遍历数据
	for rows.Next() {
		rows.Scan(ptr...)
		row := make(map[string]interface{}, count)
		for i, column := range columns {
			row[column] = value[i]
		}
		// 集合插入分片
		result = append(result, row)
	}
	return result, nil
}
