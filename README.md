### 开发环境
+ php5 
+ php_pdo
+ mysql
+ 

### 初始化开发
+ 删除 data/install.lock 文件
+ 修复可写目录权限  chmod -R 0777 ./data ./conf ./www/html ./www/attachment ./www/themes ./src/extensions ./www/windid/attachment/
+ 访问 /www/install.php 进行数据库安装

### 开发注意
+ 有增删改数据库，请再./src/applications/install/lang/目录下找到对应文件，修改相关数据库内容



more
========
请查阅https://github.com/Zerol/nextwind/wiki
