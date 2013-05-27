<?php
class Stat
{
	public static function mean($a)
	{
		$n = count($a);
		
		if($n == 0)
            return 0;
        $sum = 0;
        foreach($a as $v)
            $sum += floatval($v);
        return $sum / $n;
	}
	
	public static function variance($a, $mean = false)
	{
        $n = count($a);
        
        if($n == 0)
            return 0;
            
		if($mean === false)
			$mean = self::mean($a);
            
        $sum2 = 0;
        foreach($a as $v)
            $sum2 += pow($v - $mean, 2);
        return $sum2 / $n;
	}
	
}