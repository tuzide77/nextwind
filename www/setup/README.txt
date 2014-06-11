在升级之前，请一定仔细阅读以下注意事项：

1、由于8.7到9.0的数据结构变化很大，请先阅读8.7升级9.0数据说明，确认是否升级。

2、升级之前，请务必做好数据库、附件、头像等备份，否则出问题会导致无法恢复。

3、请注意确认是否安装周边插件，需要联系插件开发者升级。

4、确保服务器空间、数据库空间足够。 

phpwind9.0的环境准备，请确认：
PHP版本 > 5.3.x
PDO_Mysql 安装扩展已安装
Mysql版本（client）>5.x.x
附件上传 >2M



如果确认如上条件都成立，则可以准备开始升级，升级步骤如下：

1、将phpwind9.0即Nextwind安装包解压，并将upload目录下的文件上传至安装目录。
（注意，不能直接覆盖原来8.7的环境。如果是虚拟主机，建议先将原87环境除attachment目录外，移动到backup下，这样即使出现问题后可以通过移动目录恢复87的环境。） 

2、文件转移： 

2.1、头像图片转移：将原87目录下attachment/upload文件夹，拷贝到phpwind9.0的attachment目录下;（注意如果在第一步已经完成了attachment合并，则此步可忽略。）

2.2、表情图片转移：将原87目录下images/post/smile/下的所有目录拷贝到phpwind9.0的res/images/emotion/下;

2.3、勋章图片转移：将原87目录下images/medal/下的所有目录拷贝到phpwind9.0的res/images/medal/下;

注：如果下载的phpwind9.0包是含有www目录的，则将attachment包括在内的以上目录移到www目录下的对应目录中，比如res/images/emotion/则为www/res/images/emotion/

3、将升级包up87to90.php文件上传到phpwind9.0安装根目录。（如果下载的nextwind包是含有www目录的，则需要放到www目录下）;

4、确定以下目录的可写权限：

	attachment/

	conf/
	database.php

	conf/
	conf/founder.php

	conf/windidconfig.php

	data/

	data/cache/

	data/compile/

	data/design/

	data/log/

	data/tmp/

	html/

	src/extensions/

	themes/

	themes/extres/

	themes/forum/

	themes/portal/

	themes/site/

	themes/space/

5、执行升级程序访问站点的升级程序xxx.com/up87to90.php

6、填写完整需要的数据库信息，及创始人信息

7、递交之后会执行一步基本配置信息的转换

8、转换完基本配置信息之后，会正式进入主数据升级，主数据升级页面是允许多进程升级和一键升级选择的页面，在多进程升级中，您可以一次点开多个没有依赖（每步都有说明各自所需的依赖，如果没有说明则没有）的进程。
注：如果是分进程执行，请确保每一步都执行到位。
特别说明：用户头像转移该步骤是独立步骤，专门处理头像的升级，在进行该步骤之前，请先确认头像附件已经根据前面所说放置完毕，否则用户头像将采用默认头像，如果原先采用的ftp存储，则该操作将会在原ftp服务器上按照新规则生成用户头像。

9、升级执行完之后将会自动进入nextwind9的首页。

注：如果需要再次升级，请删除data/setup/setup.lock

文件


详细请查看官方论坛：

phpwind8.7to9.0升级说明：http://www.phpwind.net/read/2835356

phpwind8.7升级9.0数据说明：http://www.phpwind.net/read/2824827