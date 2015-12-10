<?php
namespace Home\Controller;

use Think\Controller;

class IndexController extends Controller
{
    public function index()
    {
        $strResult = session('result');
        if (!empty($strResult))
            $this->assign('result', $strResult);
        $this->display();
    }

    public function optimal()
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(0);
        self::func_param_empty_check(array('n', 'k', 'j', 's'));

        $data = I('post.');
        $n = str_replace(" ", "", $data['n']);
        $nArray = explode(",", $n);
        $nCount = count($nArray);

        $k = $data['k'];
        $j = $data['j'];
        $s = $data['s'];

        if ($k < $j)
            $this->error('k < j invalid input');
        if ($j < $s)
            $this->error('j < s invalid input');
        if ($k < $s)
            $this->error('k < s invalid input');

        G('begin');
        $result = self::run($nArray, $nCount, $k, $j, $s);

        $totalSubSet = count($result);
        $strResult = 'Total cost time:' .G("begin","end")."s"."<br/>".' Total optimal sub set: ' . $totalSubSet. '<br/>';
        foreach ($result as $key => $value) {
            $strResult = $strResult . '<br/>' . $value . '<br/>';
        }

//        $fp = fopen('result.html',"rw");
        file_put_contents('result.html', 'n=' . $nCount . ' k=' . $k . ' j=' . $j . ' s=' . $s .' .G("begin","end")."s".<br/>', FILE_APPEND);
        file_put_contents('result.html', $strResult, FILE_APPEND);
        session('result', $strResult);

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

    public function run($nArray, $n, $k, $j, $s)
    {
        ini_set('memory_limit', '1024M');
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
                    $matrix[$skKey]['columnCount']++;
                }
            }
        }

        $result = array();
        $exitCondition = count($s_from_j);
        do {
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

            if ($exitCondition == 0)
                break;

            $tmp = $matrix[$maxIndex];
            unset($matrix[$maxIndex]);
            // mark value 1 to 0
            foreach ($s_from_j as $sjKey => $sjValue) {
                foreach ($s_from_k as $skKey => $skValue) {
                    if ($tmp[$sjKey] == 1) {
                        if ($matrix[$skKey][$sjKey] == 1) {
                            $matrix[$skKey][$sjKey] = 0;
                            $matrix[$skKey]['columnCount']--;
                        }
                    }
                }
            }

        } while ($exitCondition != 0);
        return $result;
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