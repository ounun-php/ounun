<?php

namespace ounun\mvc\model;

class cms
{
    /** @var cms DB 相关 */
    protected static $instance;

    /**
     * @param \ounun\pdo $db
     * @return $this 返回数据库连接对像
     */
    public static function i(\ounun\pdo $db = null): self
    {
        if (empty(static::$instance)) {
            if (empty($db)) {
                $db = \v::$db_v;
            }
            static::$instance = new static($db);
        }
        return static::$instance;
    }

    /** @var \ounun\pdo */
    public $db;

    /**
     * cms constructor.
     * @param \ounun\pdo $db
     */
    public function __construct(\ounun\pdo $db = null)
    {
        if ($db) {
            $this->db = $db;
        }
        static::$instance = $this;
    }
}
