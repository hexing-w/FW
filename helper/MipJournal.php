<?php

namespace Fw\Helper;

use Fw\Helper\MipKafka;

class MipJournal
{

    /**
     * 初始化
     *
     */
    public function __construct()
    {
    }

    /**
     * 插入
     *
     * @param string $tb
     * @param string /array $data
     * @return bool
     */
    public static function add( $tb = '', $data = '' )
    {
        self::push( [ 'tb' => $tb, 'act' => 'insert', 'data' => $data ] );
    }

    /**
     * 修改
     *
     * @param string $tb
     * @param string /array $where
     * @param string /array $data
     * @return bool
     */
    public static function update( $tb = '', $where = '', $data = '' )
    {
        self::push( [ 'tb' => $tb, 'act' => 'update', 'where' => $where, 'data' => $data ] );
    }

    /**
     * 删除
     *
     * @param string $tb
     * @param string /array $where
     * @return bool
     */
    public static function delete( $tb = '', $where = '' )
    {
        self::push( [ 'tb' => $tb, 'act' => 'delete', 'where' => $where ] );
    }

    /**
     * 写入Kafka
     *
     * @param string $key
     * @param array  $data
     * @return bool
     */
    private static function push( $data = [ ] )
    {
        MipKafka::push( 'Journal', $data );
    }

}
