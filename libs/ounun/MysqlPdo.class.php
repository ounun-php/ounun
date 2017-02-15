<?php
namespace ounun;
/*###########################<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
文件:MysqlPdo.class.php
用途:MYSQL类
作者:(dreamxyp@gmail.com)[QQ31996798]
更新:2007.7.10
#############################<meta http-equiv="Content-Type" content="text/html; charset=utf-8">*/
class MysqlPdo extends \PDO
{
    /**
     * Pdo.class.php类
     *
     * @param Array $config
     */
    public function __construct($config)
    {
        $host    = explode(':',$config['host']);
        $port    = ($host[1] && is_integer($host[1]))?$host[1]:3306;
        $host    = $host[0];
        $dsn     = "mysql:dbname={$config['database']};host={$host};port={$port}";
        $options = array
        (
            \PDO::MYSQL_ATTR_INIT_COMMAND => " SET NAMES {$config['charset']} "
        );
        parent::__construct($dsn,$config['username'], $config['password'],$options);
    }
}
