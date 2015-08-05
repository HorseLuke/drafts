mysql_quirk_sort
======

怪异的MySQL分页排序查询，结果内有数据重复出现、这也意味着有部分数据永远查询不到。

表结构和数据在dump.sql。

测试版本：

MySQL 5.5.22、MariaDB 5.5.41

测试SQL：

第一次输入

```
SELECT * FROM `test_sort_table` WHERE `status` = 1 ORDER BY `sort` DESC LIMIT 0, 5;
```

第二次输入
```
SELECT * FROM `test_sort_table` WHERE `status` = 1 ORDER BY `sort` DESC LIMIT 40, 5;
```

问题：

为什么id 42出现了两次？这本来不应该出现这种情况。

为什么删掉content字段后会正常？

为什么content字段会意外影响sort排序，即使content字段不在任何一个索引中？

以下为测试SQL和结果：

```
mysql> SELECT version();
+------------+
| version()  |
+------------+
| 5.5.22-log |
+------------+
1 row in set (0.00 sec)

mysql> use test
Database changed

mysql> SELECT * FROM `test_sort_table` WHERE `status` = 1 ORDER BY `sort` DESC LIMIT 0, 5;
+----+---------+--------+------+
| id | content | status | sort |
+----+---------+--------+------+
|  2 |         |      1 |   22 |
|  1 |         |      1 |    1 |
| 42 |         |      1 |    0 |
| 41 |         |      1 |    0 |
| 40 |         |      1 |    0 |
+----+---------+--------+------+
5 rows in set (0.00 sec)

mysql> SELECT * FROM `test_sort_table` WHERE `status` = 1 ORDER BY `sort` DESC LIMIT 40, 5;
+----+---------+--------+------+
| id | content | status | sort |
+----+---------+--------+------+
| 42 |         |      1 |    0 |
+----+---------+--------+------+
1 row in set (0.00 sec)

mysql>
```
