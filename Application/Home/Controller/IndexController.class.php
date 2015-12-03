<?php
namespace Home\Controller;

use Think\Controller;

class IndexController extends Controller
{
    public function index()
    {
        $strResult = session('result');
        if(!empty($strResult))
            $this->assign('result',$strResult);
        $this->display();
    }

    public function optimal()
    {
        ini_set('memory_limit','1024M');
        self::func_param_empty_check(array('n','k','j','s'));

        $data = I('post.');
        $n = str_replace(" ", "", $data['n']);
        $nArray = explode(",", $n);
        $nCount = count($nArray);

        $k = $data['k'];
        $j = $data['j'];
        $s = $data['s'];

        if($k < $j)
            $this->error('k < j invalid input');
        if($j < $s)
            $this->error('j < s invalid input');
        if($k < $s)
            $this->error('k < s invalid input');

        if($k > $j && $j == $s)
            $result = self::conditionOne($nArray,$nCount,$k,$j);
        else
            $result = self::conditionTwo($nArray,$nCount,$k,$j,$s);
        $strResult = '';
        foreach($result as $key => $value){
            $strResult = $strResult.'<br/>'.$value.'<br/>';
        }
        // echo $strResult;
        // $this->assign('result',$strResult);
        session('result',$strResult);
        $this->redirect('index');
    }

    /**
     * 檢查提交的參數是否爲空
     * @param array $keys
     */
    protected function func_param_empty_check($keys = array(), $makeUp = array())
    {
        for ($i = 0; $i < count($keys); $i++) {
            if (empty(I($keys[$i]))) {
                !empty($makeUp) ? $this->error($makeUp[$i] . " can not be empty.") : $this->error($keys[$i] . " can not be empty.");
                die;
            }
        }
    }

    /**
     * s=j<k
     */
    public function conditionOne($nArray, $n, $k, $j)
    {
        $kCombination = array(); // k elements combination
        $jCombination = array(); // j elements combination
        self::combination($nArray, $n, $k, $kCombination, $k);
        self::combination($nArray, $n, $j, $jCombination, $j);
        sort($kCombination);
        sort($jCombination);
//        var_dump($A);
//        var_dump($B);
        $j_from_k = array();
        $hitMap = array(); // each $A hit map, count is the ratio
        foreach ($kCombination as $key => $value) {
            $itemArray = explode(' ', $value); // use blank to split string to array
            $itemSize = count($itemArray);
            $C = array();

            self::combination($itemArray, $itemSize, $j, $C, $j);
            $j_from_k[$key] = $C;
//            var_dump($C);
            $countC = count($C);
            for ($m = 0; $m < $countC; $m++) {
                $index = array_search($C[$m], $jCombination);
                if ($index !== false) { // search success, must use !== to prevent 0 case
                    $hitMap[$key][$jCombination[$index]] = 1;
                }
            }
        }
//        var_dump($hitMap);
        $result = array();
        do {
            $index = self::maxHit($hitMap);
//            var_dump($index);
            array_push($result, $kCombination[$index]);
            unset($kCombination[$index]);
            unset($j_from_k[$index]);
            $excludeItem = array_keys($hitMap[$index]); // transform to do diff opt
//            var_dump($excludeItem);
            $jCombination = array_diff($jCombination, $excludeItem);
//            var_dump($B);
            unset($hitMap);
            foreach ($j_from_k as $key => $value) {
                $countC = count($value);
                for ($m = 0; $m < $countC; $m++) {
                    $index = array_search($value[$m], $jCombination);
                    if ($index !== false) { // search success, must use !== to prevent 0 case
                        $hitMap[$key][$jCombination[$index]] = 1;
                    }
                }
            }
//            var_dump($hitMap);
        } while (!empty($jCombination));

//        var_dump($result);
        return $result;
    }

    /**
     * s < j <= k
     */
    public function conditionTwo($nArray, $n, $k, $j, $s)
    {

        $kCombination = array();
        $jCombination = array();

        self::combination($nArray, $n, $k, $kCombination, $k); // k from n
        self::combination($nArray, $n, $j, $jCombination, $j); // j from n

        // var_dump($kCombination);
        // var_dump($jCombination);
        
        sort($kCombination);
        sort($jCombination);
        
        $s_from_k = array();
        foreach ($kCombination as $skKey => $skValue) {
            $tmp = explode(' ', $skValue);
            $item = array();
            self::combination($tmp, $k, $s, $item, $s);
            $s_from_k[$skKey] = $item;
        }

//        var_dump($s_from_k);

        $s_from_j = array();
        foreach ($jCombination as $skKey => $skValue) {
            $tmp = explode(' ', $skValue);
            $item = array();
            self::combination($tmp, $j, $s, $item, $s);
            $s_from_j[$skKey] = $item;
        }

//        var_dump($s_from_j);

        $matrix = array();
        foreach ($s_from_k as $skKey => $skValue) {
            foreach ($s_from_j as $sjKey => $sjValue) {
                $intersection = array_intersect($skValue, $sjValue); // cal intersection. if no empty set matrix($skKey , $sjKey) = 1, otherwise 0
                if (empty($intersection)) {
                    $matrix[$skKey][$sjKey] = 0;
                } else {
                    $matrix[$skKey][$sjKey] = 1;
                    if (!isset($matrix[$skKey]['columnCount'])) // count of 1 value
                        $matrix[$skKey]['columnCount'] = 1;
                    else
                        $matrix[$skKey]['columnCount']++;
                }

            }
        }

//        var_dump($matrix);

        $result = array();
        $exitCondition = count($s_from_j);
        // p($matrix);
        // die;
        // p($s_from_j);
        do{
            // init with the first element
            $keyValue = array_keys($matrix);
            $maxIndex = $keyValue[0];
            $maxColumn = $matrix[$keyValue[0]]['columnCount'];
            // find the max columnCount
            foreach ($s_from_k as $skKey => $skValue) {
                if ($matrix[$skKey]['columnCount'] > $maxColumn) {
                    $maxIndex = $skKey;
                    $maxColumn = $matrix[$skKey]['columnCount'];
                }
            }
            array_push($result, $kCombination[$maxIndex]);
            $exitCondition -= $maxColumn;

            $tmp = $matrix[$maxIndex];
            unset($matrix[$maxIndex]);
            // mark value 1 to 0
            foreach ($s_from_j as $sjKey => $sjValue) {
                foreach ($s_from_k as $skKey => $skValue) {
                    if($tmp[$sjKey] == 1){
                        $matrix[$skKey][$sjKey] = 0;
                        $matrix[$skKey]['columnCount'] --;
                    }
                }
            }
            p($exitCondition);
            // die;
        }while($exitCondition != 0);
//        var_dump($result);
        // var_dump($s_from_k);
        return $result;
    }

    /**
     * max count in array
     */
    private static function maxHit($variable)
    {
        $index = 0;
        $compare = count($variable[0]);
        foreach ($variable as $key => $value) {
            $tmp = count($value);
            if ($tmp > $compare)
                $index = $key;
        }
        return $index;
    }

    /**
     * Generating combination
     * @param $candidate The candidate array
     * @param $n size of candidate array
     * @param $m how many you want to pick
     * @param $result collections of the combination
     * @param $M equal to $m
     * @param $index  index of array with each result
     */
    private static function combination($candidate, $n, $m, &$result, $M, &$index = array())
    {
        for ($i = $n; $i >= $m; $i--) {
            $index[$m - 1] = $i - 1;
            if ($m > 1)
                self::combination($candidate, $i - 1, $m - 1, $result, $M, $index);
            else {
                $str = '';
                for ($j = 0; $j <= $M - 1; $j++) {
                    $str .= ' ' . $candidate[$index[$j]];
                }
                $str = substr($str, 1); // exclude first blank
                $result[] = $str;
            }
        }
    }

}