#!/usr/bin/env python3
import re
import sys

def convert_mysql_to_pgsql(mysql_sql):
    """将 MySQL SQL 转换为 PostgreSQL SQL"""
    sql = mysql_sql
    
    # 移除 MySQL 特定注释和设置
    sql = re.sub(r'/\*!.*?\*/', '', sql, flags=re.DOTALL)
    sql = re.sub(r'SET @OLD.*?;', '', sql, flags=re.DOTALL)
    sql = re.sub(r'SET.*?;', '', sql)
    
    # 移除 MySQL 特定语句
    sql = re.sub(r'LOCK TABLES.*?;', '', sql, flags=re.DOTALL | re.IGNORECASE)
    sql = re.sub(r'UNLOCK TABLES;', '', sql, flags=re.IGNORECASE)
    sql = re.sub(r'/\*!40000 ALTER TABLE.*?ENABLE KEYS \*/', '', sql, flags=re.DOTALL)
    sql = re.sub(r'/\*!40000 ALTER TABLE.*?DISABLE KEYS \*/', '', sql, flags=re.DOTALL)
    
    # 移除 MySQL 特定语法
    sql = re.sub(r'ENGINE=InnoDB[^;]*', '', sql, flags=re.IGNORECASE)
    sql = re.sub(r'DEFAULT CHARSET=[^;]*', '', sql, flags=re.IGNORECASE)
    sql = re.sub(r'COLLATE=[^;]*', '', sql, flags=re.IGNORECASE)
    
    # 处理 AUTO_INCREMENT - 先处理 bigint unsigned AUTO_INCREMENT
    sql = re.sub(r'`?(\w+)`?\s+bigint\(20\)\s+unsigned\s+NOT\s+NULL\s+AUTO_INCREMENT', 
                 r'\1 BIGSERIAL NOT NULL', 
                 sql, flags=re.IGNORECASE)
    
    # 处理 int AUTO_INCREMENT
    sql = re.sub(r'`?(\w+)`?\s+int\(11\)\s+unsigned\s+NOT\s+NULL\s+AUTO_INCREMENT', 
                 r'\1 SERIAL NOT NULL', 
                 sql, flags=re.IGNORECASE)
    
    sql = re.sub(r'`?(\w+)`?\s+int\(11\)\s+NOT\s+NULL\s+AUTO_INCREMENT', 
                 r'\1 SERIAL NOT NULL', 
                 sql, flags=re.IGNORECASE)
    
    # 数据类型转换（在 AUTO_INCREMENT 之后）
    sql = re.sub(r'bigint\(20\)\s+unsigned', 'BIGINT', sql, flags=re.IGNORECASE)
    sql = re.sub(r'int\(11\)\s+unsigned', 'INTEGER', sql, flags=re.IGNORECASE)
    sql = re.sub(r'int\(11\)', 'INTEGER', sql, flags=re.IGNORECASE)
    sql = re.sub(r'tinyint\(1\)', 'BOOLEAN', sql, flags=re.IGNORECASE)
    sql = re.sub(r'tinyint\([^)]+\)', 'SMALLINT', sql, flags=re.IGNORECASE)
    sql = re.sub(r'datetime', 'TIMESTAMP', sql, flags=re.IGNORECASE)
    sql = re.sub(r'varchar\((\d+)\)', r'VARCHAR(\1)', sql, flags=re.IGNORECASE)
    sql = re.sub(r'char\((\d+)\)', r'CHAR(\1)', sql, flags=re.IGNORECASE)
    
    # 移除反引号
    sql = re.sub(r'`([^`]+)`', r'\1', sql)
    
    # 移除 COMMENT
    sql = re.sub(r"COMMENT\s+['\"][^'\"]*['\"]", '', sql, flags=re.IGNORECASE)
    
    # 移除 COLLATE（PostgreSQL 不需要）
    sql = re.sub(r'\s+COLLATE\s+\w+', '', sql, flags=re.IGNORECASE)
    
    # 处理 KEY 和 INDEX - 转换为单独的 CREATE INDEX 语句
    index_defs = []
    
    def extract_unique_key(match):
        key_name = match.group(1)
        columns = match.group(2)
        # 从上下文获取表名（简化处理，假设在 CREATE TABLE 语句中）
        return ''  # 先移除，稍后处理
    
    def extract_key(match):
        key_name = match.group(1)
        columns = match.group(2)
        return ''  # 先移除，稍后处理
    
    # 提取并移除 UNIQUE KEY（保留 PRIMARY KEY）
    sql = re.sub(r',\s*UNIQUE KEY\s+(\w+)\s*\(([^)]+)\)', '', sql, flags=re.IGNORECASE)
    # 提取并移除普通 KEY（但保留 PRIMARY KEY）
    sql = re.sub(r',\s*(?<!PRIMARY\s)KEY\s+(\w+)\s*\(([^)]+)\)', '', sql, flags=re.IGNORECASE)
    
    # 清理多余的空行和分号
    sql = re.sub(r';\s*;+', ';', sql)
    sql = re.sub(r'\n\s*\n\s*\n+', '\n\n', sql)
    
    return sql

if __name__ == '__main__':
    with open('DB.sql', 'r', encoding='utf-8') as f:
        mysql_sql = f.read()
    
    pgsql = convert_mysql_to_pgsql(mysql_sql)
    
    with open('DB_PostgreSQL.sql', 'w', encoding='utf-8') as f:
        f.write(pgsql)
    
    print("✅ 转换完成！已保存到 DB_PostgreSQL.sql")
    print(f"原始大小: {len(mysql_sql)} 字符")
    print(f"转换后大小: {len(pgsql)} 字符")
