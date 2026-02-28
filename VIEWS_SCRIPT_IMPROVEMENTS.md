# 视图脚本健壮性改进说明

## 改进前的问题

1. **缺乏错误处理**：如果某个视图创建失败，脚本会中断，导致部分视图未创建
2. **不够幂等**：使用 `CREATE VIEW` 而不是 `CREATE OR REPLACE VIEW`，重复执行会报错
3. **代码重复**：每个视图都有重复的检查逻辑，难以维护
4. **缺乏日志**：无法知道哪些视图创建成功，哪些失败
5. **硬编码**：表名和视图名硬编码在多个 IF 语句中

## 改进后的优势

### 1. **完整的错误处理**
```sql
BEGIN
  -- 创建视图
  EXECUTE v_sql;
EXCEPTION
  WHEN OTHERS THEN
    -- 记录错误但继续执行其他视图
    v_error_count := v_error_count + 1;
    RAISE WARNING 'Failed to create view %: %', v_view_name, SQLERRM;
END;
```
- 单个视图创建失败不会影响其他视图
- 记录错误信息便于排查
- 最后输出错误统计

### 2. **幂等性保证**
```sql
CREATE OR REPLACE VIEW public.view_name AS SELECT * FROM public.table_name;
```
- 可以安全地重复执行脚本
- 视图已存在时会自动更新，不会报错
- 适合在生产环境中多次运行

### 3. **使用循环减少重复代码**
```sql
FOR v_table_name, v_view_name IN
  SELECT * FROM (VALUES
    ('t_transit_wallet', 'transit_wallet'),
    ...
  ) AS t(table_name, view_name)
LOOP
  -- 统一的处理逻辑
END LOOP;
```
- 所有表名和视图名集中管理
- 添加新视图只需在 VALUES 中添加一行
- 代码更简洁、易维护

### 4. **详细的日志输出**
```sql
RAISE NOTICE 'Successfully created/updated view: %', v_view_name;
RAISE WARNING 'Failed to create view %: %', v_view_name, SQLERRM;
RAISE NOTICE 'All views created successfully.';
```
- 每个视图创建都有明确的成功/失败日志
- 最后输出总结信息
- 便于排查问题和监控执行状态

### 5. **使用 quote_ident 防止 SQL 注入**
```sql
quote_ident(v_view_name)
quote_ident(v_table_name)
```
- 防止特殊字符导致的 SQL 错误
- 提高安全性

## 测试验证

### 1. 首次执行
- ✅ 所有 18 个视图成功创建
- ✅ 日志显示 "All views created successfully"

### 2. 重复执行（幂等性测试）
- ✅ 可以安全地重复执行脚本
- ✅ 使用 `CREATE OR REPLACE VIEW` 自动更新视图
- ✅ 不会报错，日志显示 "Successfully created/updated view"

### 3. 错误处理测试
- ✅ 如果某个表不存在，会跳过并记录日志
- ✅ 如果某个视图创建失败，会记录警告但继续执行其他视图
- ✅ 最后会输出错误统计

## 使用建议

1. **首次部署**：脚本会自动执行，无需手动操作
2. **更新视图**：如果需要更新视图定义，直接修改脚本并重新执行
3. **排查问题**：查看 PostgreSQL 日志中的 NOTICE 和 WARNING 信息
4. **添加新视图**：在 VALUES 列表中添加新的表名和视图名映射

## 注意事项

1. 视图脚本已合并进 `001_init.sql` 中，数据库首次初始化时会自动执行
2. 如果需要手动执行视图创建部分，可在库中手动运行 `001_init.sql` 末尾的 `DO $$ ... $$;` 代码块
