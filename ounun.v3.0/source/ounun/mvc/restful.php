<?php
namespace ounun\mvc;


abstract class restful
{
    /** @var array */
    protected $_mod;
    /** @var \v */
    protected $_v;
    /** @var \ounun\restful */
    protected $_restful;

    public function __construct(array $mod = [],?\ounun\restful $restful = null)
    {
        $this->_mod = $mod;
        $this->_restful = $restful??new \ounun\restful($mod);

        $m = $this->_restful->method_get();
        if('GET' == $m || 'POST' ==  $m || 'PUT' == $m || 'DELETE' == $m){
            $this->$m();
        }else {
            $this->GET();
        }
    }

    /** GET 返回资源信息 */
    abstract public function GET();
    /** POST 创建资源信息 */
    abstract public function POST();
    /** PUT 更新资源信息 */
    abstract public function PUT();
    /** DELETE 删除资源信息 */
    abstract public function DELETE();
}
