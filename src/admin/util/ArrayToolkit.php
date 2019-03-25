<?php

namespace app\admin\util;

class ArrayToolkit
{

    public static function sortByField(array $array, $field, $sort = 'desc')
    {
        $list = array_values($array);
        $count = count($list);
        if($count <= 0)
        {
            return $array;
        }
        for ($i=0; $i <$count ; $i++) {

            for ($j=0; $j <$count-$i-1 ; $j++) {
                $condition = $sort == 'asc' ? ($list[$j][$field] > $list[$j+1][$field]) : ($list[$j][$field] < $list[$j+1][$field]);
                if ($condition) {
                    $tmp = $list[$j+1];
                    $list[$j+1]=$list[$j];
                    $list[$j]=$tmp;
                }
            }
        }
        return $list;
    }

    public static function fields(array $data, $fields)
    {
        $return = [];
        foreach ($fields as $v)
        {
            if(isset($data[$v]))
            {
                $return[$v] = $data[$v];
            }
        }
        return $return;
    }

    public static function fieldValue(array $array, $field, $value)
    {
        $data = [];
        foreach ($array as $k => $v)
        {
            if(isset($v[$field]) && $v[$field] == $value)
            {
                $data[] = $v;
            }
        }
        return $data;
    }

    public static function get(array $array, $key, $default)
    {
        if (isset($array[$key])) {
            return $array[$key];
        } else {
            return $default;
        }
    }

    public static function column(array $array, $columnName)
    {
        if (empty($array)) {
            return array();
        }

        $column = array();

        foreach ($array as $item) {
            if (isset($item[$columnName])) {
                $column[] = $item[$columnName];
            }
        }

        return $column;
    }

    public static function columns(array $array, array $columnNames)
    {
        if (empty($array) or empty($columnNames)) {
            return array();
        }

        $columns = array();

        foreach ($array as $item) {
            foreach ($columnNames as $key) {
                $value = isset($item[$key]) ? $item[$key] : '';
                $columns[$key][] = $value;
            }
        }

        return array_values($columns);
    }

    public static function parts(array $array, array $keys)
    {
        foreach (array_keys($array) as $key) {
            if (!in_array($key, $keys)) {
                unset($array[$key]);
            }
        }

        return $array;
    }

    public static function requireds(array $array, array $keys, $strictMode = false)
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $array)) {
                return false;
            }
            if ($strictMode && (is_null($array[$key]) || $array[$key] === '' || $array[$key] === 0)) {
                return false;
            }
        }

        return true;
    }

    public static function changes(array $before, array $after)
    {
        $changes = array('before' => array(), 'after' => array());

        foreach ($after as $key => $value) {
            if (!isset($before[$key])) {
                continue;
            }

            if ($value != $before[$key]) {
                $changes['before'][$key] = $before[$key];
                $changes['after'][$key] = $value;
            }
        }

        return $changes;
    }

    public static function group(array $array, $key)
    {
        $grouped = array();

        foreach ($array as $item) {
            if (empty($grouped[$item[$key]])) {
                $grouped[$item[$key]] = array();
            }

            $grouped[$item[$key]][] = $item;
        }

        return $grouped;
    }

    public static function index(array $array, $name)
    {
        $indexedArray = array();

        if (empty($array)) {
            return $indexedArray;
        }

        foreach ($array as $item) {
            if (isset($item[$name])) {
                $indexedArray[$item[$name]] = $item;
                continue;
            }
        }

        return $indexedArray;
    }

    public static function groupIndex(array $array, $key, $index)
    {
        $grouped = array();

        foreach ($array as $item) {
            if (empty($grouped[$item[$key]])) {
                $grouped[$item[$key]] = array();
            }

            $grouped[$item[$key]][$item[$index]] = $item;
        }

        return $grouped;
    }

    public static function rename(array $array, array $map)
    {
        $keys = array_keys($map);

        foreach ($array as $key => $value) {
            if (in_array($key, $keys)) {
                $array[$map[$key]] = $value;
                unset($array[$key]);
            }
        }

        return $array;
    }

    public static function filter(array $array, array $specialValues)
    {
        $filtered = array();

        foreach ($specialValues as $key => $value) {
            if (!array_key_exists($key, $array)) {
                continue;
            }

            if (is_array($value)) {
                $filtered[$key] = (array) $array[$key];
            } elseif (is_int($value)) {
                $filtered[$key] = (int) $array[$key];
            } elseif (is_float($value)) {
                $filtered[$key] = (float) $array[$key];
            } elseif (is_bool($value)) {
                $filtered[$key] = (bool) $array[$key];
            } else {
                $filtered[$key] = (string) $array[$key];
            }

            if (!isset($filtered[$key])) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    public static function trim($array)
    {
        if (!is_array($array)) {
            return $array;
        }

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = static::trim($value);
            } elseif (is_string($value)) {
                $array[$key] = trim($value);
            }
        }

        return $array;
    }

    public static function every($array, $callback = null)
    {
        foreach ($array as $value) {
            if ((is_null($callback) && !$value) || (is_callable($callback) && !$callback($value))) {
                return false;
            }
        }

        return true;
    }

    public static function some($array, $callback = null)
    {
        foreach ($array as $value) {
            if ((is_null($callback) && $value) || (is_callable($callback) && $callback($value))) {
                return true;
            }
        }

        return false;
    }

    /**
     * 二维数组合并值，返回去除重复值的一维数组.
     *
     * @param [type] $doubleArrays [description]
     *
     * @return [type] [description]
     */
    public static function mergeArraysValue($doubleArrays)
    {
        $values = array();
        foreach ($doubleArrays as $array) {
            if (empty($array)) {
                continue;
            }
            foreach ($array as $value) {
                if (in_array($value, $values)) {
                    continue;
                }
                $values[] = $value;
            }
        }

        return $values;
    }

    public static function thin(array $array, array $columns)
    {
        $thinner = array();
        foreach ($array as $k => $v) {
            foreach ($columns as $v2) {
                $thinner[$k][$v2] = $v[$v2];
            }
        }

        unset($array);

        return $thinner;
    }




    /**
     * 输出xml字符
     * @param array $values
     * @return string|bool
     **/
    public static function toXml($values)
    {
        if (!is_array($values) || count($values) <= 0) {
            return false;
        }

        $xml = "<xml>";
        foreach ($values as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    /**
     * 将xml转为array
     * @param string $xml
     * @return array|false
     */
    public static function  toArray($xml)
    {
        if (!$xml) {
            return false;
        }

        // 检查xml是否合法
        $xml_parser = xml_parser_create();
        if (!xml_parse($xml_parser, $xml, true)) {
            xml_parser_free($xml_parser);
            return false;
        }

        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);

        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

        return $data;
    }



    public static function createMD5Sign($param,$key) {
        $signPars = "";
        ksort($param);
        foreach($param as $k => $v) {
            if("" != $v && "sign" != $k) {
                $signPars .= $k . "=" . $v . "&";
            }
        }
        $signPars .= "key=" . $key;
        $sign = strtoupper(md5($signPars));
        return  $sign;
    }
}
