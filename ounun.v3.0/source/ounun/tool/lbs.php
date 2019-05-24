<?php

namespace ounun\tool;


class lbs
{
    /** @var float 地球半径 */
    const Earth_Radius = 6371.393;

    /**
     * 查找附近的门店
     * @param float $longitude 经度
     * @param float $latitude 纬度
     * @param float $distince 距离 (千米)
     * @return array 符合距离范围的所有的点
     */
    static public function get_nearby_store(float $longitude, float $latitude, float $distince)
    {
        list($minlng, $maxlng, $minlat, $maxlat) = static::get_nearby_by_longitude_and_latitude_and_distince($longitude, $latitude, $distince);

        $m = 10000000000;
        $r_lng = rand(1,$m);
        $r_lat = rand(1,$m);
        $i_lng = $minlng + $r_lng/$m * ($maxlng - $minlng);
        $i_lat = $minlat + $r_lat/$m * ($maxlat - $minlat);
        // print_r([$minlng, $maxlng, $minlat, $maxlat,'lng'=>$i_lng,'lat'=>$i_lat]);
        return [$i_lng,$i_lat]; // return static::find_nearby_store($minlng, $maxlng, $minlat, $maxlat);
    }

    /**
     * 计算给定经纬度附近相应公里数的经纬度范围
     * @param double $longitude 经度
     * @param double $latitude 纬度
     * @param double $distince 距离（千米）
     * @return array 格式：[经度最小值,经度最大值,纬度最小值,纬度最大值]
     **/
    static public function get_nearby_by_longitude_and_latitude_and_distince(float $longitude, float $latitude, float $distince)
    {
        $r = static::Earth_Radius;    // 地球半径千米

        $dlng = 2 * asin(sin($distince / (2 * $r)) / cos($latitude * M_PI / 180));
        $dlng = $dlng * 180 / M_PI;// 角度转为弧度
        $dlat = $distince / $r;
        $dlat = $dlat * 180 / M_PI;

        $minlat = $latitude - $dlat;
        $maxlat = $latitude + $dlat;
        $minlng = $longitude - $dlng;
        $maxlng = $longitude + $dlng;

        return [$minlng, $maxlng, $minlat, $maxlat];
    }

    /**
     * @Description  计算距离远近并按照距离排序
     * @param float $longitude 经度
     * @param float $latitude  纬度
     * @param array $nearbyStoreList 附近门店
     * @return array 按照距离由近到远排序之后List
     */
    static public function get_nearby_store_by_distince_asc(float $longitude, float $latitude, array $nearbyStoreList)
    {
        $list = [];
        foreach ($nearbyStoreList as $v) {
            $dis = static::distince($longitude, $latitude, $v['longitude'], $v['latitude']);
            $dis = (string)$dis;
            $list[$dis] = $v;
        }
        ksort($list);
        return $list;
    }

    /**
     * @Description 根据经纬度获取两点之间的距离
     * @param float $longitude1 地点1经度
     * @param float $latitude1 地点1纬度
     * @param float $longitude2 地点2经度
     * @param float $latitude2 地点2纬度
     * @return  float  距离：单位 米
     */
    static public function distince(float $longitude1, float $latitude1, float $longitude2, float $latitude2)
    {
        $r = static::Earth_Radius;         // 地球半径千米

//      double lat1 = latitude1.doubleValue();
//      double lng1 = longitude1.doubleValue();
//      double lat2 = latitude2.doubleValue();
//      double lng2 = longitude2.doubleValue();

        $radLat1 = static::rad($latitude1);
        $radLat2 = static::rad($latitude2);
        $a = $radLat1 - $radLat2;
        $b = static::rad($longitude1) - static::rad($longitude2);
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) +
                 cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
        $s = $s * $r;
        $s = round($s * 1000);
        return $s;
    }

    /**
     * @param float $d
     * @return float
     */
    static protected function rad(float $d)
    {
        return $d * M_PI / 180.0;
    }
}