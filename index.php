<?php
//print_r(getallheaders());
//exit();

function imagemonospaced($image, $x, $y, $string, $color, $width) {
    for ($i=0; $i>strlen($string); $i++) {
        imagestring($image, $x+$width*$i, $y, $string, $color);
    }
}

function imagelinethick($image, $x1, $y1, $x2, $y2, $color, $thick = 1) {
    // this way it works well only for orthogonal lines
    imagesetthickness($image, $thick);
    imagefilledellipse($image, $x1, $y1, $thick, $thick, $color);
    imagefilledellipse($image, $x2, $y2, $thick, $thick, $color);
    return imageline($image, $x1, $y1, $x2, $y2, $color);
    if ($thick == 1) {
        return imageline($image, $x1, $y1, $x2, $y2, $color);
    }
    $t = $thick / 2 - 0.5;
    if ($x1 == $x2 || $y1 == $y2) {
        return imagefilledrectangle($image, round(min($x1, $x2) - $t), round(min($y1, $y2) - $t), round(max($x1, $x2) + $t), round(max($y1, $y2) + $t), $color);
    }
    $k = ($y2 - $y1) / ($x2 - $x1); //y = kx + q
    $a = $t / sqrt(1 + pow($k, 2));
    $points = array(
        round($x1 - (1+$k)*$a), round($y1 + (1-$k)*$a),
        round($x1 - (1-$k)*$a), round($y1 - (1+$k)*$a),
        round($x2 + (1+$k)*$a), round($y2 - (1-$k)*$a),
        round($x2 + (1-$k)*$a), round($y2 + (1+$k)*$a),
    );
    imagefilledpolygon($image, $points, 4, $color);
    return imagepolygon($image, $points, 4, $color);
}

function drawclockarm($image, $cx, $cy, $len, $angle, $color, $thick = 1, $startlen = 0) {
    $ey = round($cy + sin(($angle)*(pi()/180))*$len/sin(pi()*0.5));
    $ex = round($cx + (sin((90-$angle)*(pi()/180))*$len)/sin(pi()*0.5));
    if ($startlen!==0) {
        $cy = round($cy + sin(($angle)*(pi()/180))*$startlen/sin(pi()*0.5));
        $cx = round($cx + (sin((90-$angle)*(pi()/180))*$startlen)/sin(pi()*0.5));
    }

    imagelinethick($image, $cx, $cy, $ex, $ey, $color, $thick);
}

function get_timezone_abbreviation($timezone_id) {
    if($timezone_id){
        $abb_list = timezone_abbreviations_list();
        foreach ($abb_list as $abb_key => $abb_val) {
            foreach ($abb_val as $key => $value) {
                if($value['timezone_id'] == $timezone_id){
                    return strtoupper($abb_key);
                }
            }
        }
    }
    return FALSE;
}

function get_timezone_region($timezone_abbr) {
    if($timezone_abbr){
        $reg_list = timezone_abbreviations_list();
        foreach ($reg_list as $reg_key => $reg_val) {
            if ($reg_key === strtolower($timezone_abbr)) {
                if (count($reg_val)>1) {
                    return $reg_val[1]["timezone_id"];
                }
                return $reg_val[0]["timezone_id"];
            }
        }
    }
    return FALSE;
}

if (!isset($_GET["timezone"])) {
    $clitimezone_abbr="UTC";
    $clitimezone_region=get_timezone_region($clitimezone_abbr);
} elseif (strpos($_GET["timezone"], '/') === false) {
    $clitimezone_region=get_timezone_region($_GET["timezone"]);
    $clitimezone_abbr=$_GET["timezone"];
} else {
    $clitimezone_region=$_GET["timezone"];
    $clitimezone_abbr=get_timezone_abbreviation($clitimezone_region);
}
if ($clitimezone_abbr === false or $clitimezone_region === false) {
    exit("timezone not found");
}
date_default_timezone_set($clitimezone_region);
$clientip = '';
if (getenv('HTTP_CLIENT_IP'))
    $clientip = getenv('HTTP_CLIENT_IP');
else if(getenv('HTTP_X_FORWARDED_FOR'))
    $clientip = getenv('HTTP_X_FORWARDED_FOR');
else if(getenv('HTTP_X_FORWARDED'))
    $clientip = getenv('HTTP_X_FORWARDED');
else if(getenv('HTTP_FORWARDED_FOR'))
    $clientip = getenv('HTTP_FORWARDED_FOR');
else if(getenv('HTTP_FORWARDED'))
   $clientip = getenv('HTTP_FORWARDED');
else if(getenv('REMOTE_ADDR'))
    $clientip = getenv('REMOTE_ADDR');
else
    $clientip = 'UNKNOWN';

$width = 660;
$height = 250;
$im = @imagecreatetruecolor($width, $height)
    or die('Cannot Initialize new GD image stream');

imagesavealpha($im, true);
$trans_colour = imagecolorallocatealpha($im, 0, 0, 0, 127);
imagefill($im, 0, 0, $trans_colour);

$black = imagecolorallocate($im, 0, 0, 0);
$dark_gray = imagecolorallocate($im, 63, 63, 63);
$white = imagecolorallocate($im, 255,255,255);
$red = imagecolorallocate($im, 255,0,0);

//drawing the clocks
$elly=120;
$ellwid=150;
$ellxoffset=110;

imagefilledellipse($im, $ellxoffset, $elly, $ellwid, $ellwid, $dark_gray);
imagefilledellipse($im, $ellxoffset+($width/2), $elly, $ellwid, $ellwid, $dark_gray);
for ($x=0; $x < 2; $x++) {
    for ($i=0; $i < 12; $i++) {
        $angle = (360/12) * $i - 90;
        $len = $ellwid/2 - 4;
        $startlen = 0.9*($ellwid/2);
        drawclockarm($im, $ellxoffset + $x * $width/2, $elly, $len, $angle, $white, 4, $startlen);

        $len = 0.75*($ellwid/2);
        $y1 = round($elly + sin(($angle)*(pi()/180))*$len/sin(pi()*0.5));
        $x1 = round($ellxoffset+$x*($width/2) + (sin((90-$angle)*(pi()/180))*$len)/sin(pi()*0.5));
        if ($i === 0) {
            imagestring($im, 3, $x1 - 6, $y1 - 7, 12, $white);
            continue;
        } elseif ($i >=10) {
            imagestring($im, 3, $x1 - 6, $y1 - 7, $i, $white);
            continue;
        }
        imagestring($im, 3, $x1 - 2, $y1 - 7, $i, $white);
    }
}
$hpd = array('width' => 8, 'len' => 0.4, 'degrees' => 360/12);
$mpd = array('width' => 4, 'len' => 0.6, 'degrees' => 360/60);
$spd = array('width' => 2, 'len' => 0.7, 'degrees' => 360/60);
if (isset($_GET["s"]) and isset($_GET["s"]) and isset($_GET["m"]) and isset($_GET["h"]) and isset($_GET["a"]) and isset($_GET["d"]) and isset($_GET["M"]) and isset($_GET["y"])) {
    $times = Array(intval($_GET["s"]),intval($_GET["m"]),intval($_GET["h"]),$_GET["a"],intval($_GET["d"]),$_GET["M"],intval($_GET["y"]));
} else {
    if (!isset($_GET["timestamp"])) {
        $_GET["timestamp"]=time();
    }
    $times = explode(":",date("s:i:g:a:j:M:Y",intval($_GET["timestamp"])));
    foreach ($_GET as $key => $val) {
        switch ($key) {
            case 's':
                $times[0]=$_GET["s"];
                break;
            case 'm':
                $times[1]=$_GET["m"];
                break;
            case 'h':
                $times[2]=$_GET["h"];
                break;
            case 'a':
                $times[3]=$_GET["a"];
                break;
            case 'd':
                $times[4]=$_GET["d"];
                break;
            case 'M':
                $times[5]=$_GET["M"];
                break;
            case 'y':
                $times[6]=$_GET["y"];
                break;
        }
    }
    $times = Array(intval($times[0]),intval($times[1]),intval($times[2]),$times[3],intval($times[4]),$times[5],intval($times[6]));
}

$ip_info = json_decode(file_get_contents("http://ip-api.com/json/$clientip"),true);
imagestring($im, 5, $ellxoffset-$ellwid/2, 15, $clitimezone_abbr, $dark_gray);
imagestring($im, 5, $ellxoffset-$ellwid/2+($width/2), 15, $ip_info["city"].", ".$ip_info["country"]." - ".get_timezone_abbreviation($ip_info["timezone"]), $dark_gray);

for ($x=0;$x<2;$x++) {
    $ellx=$x * ($width/2) + $ellxoffset;
    if ($x === 1) {
        date_default_timezone_set($clitimezone_region);
        $servdate = date("Y-m-d h:i:s A"); //Lexington, USA

        date_default_timezone_set($ip_info["timezone"]);
        $clidate = date("Y-m-d h:i:s A");

        $timediff = strtotime($clidate) - strtotime($servdate);
        while ($timediff!==0){
            if($timediff<0) {
                if ($timediff>(-60)) {
                    $times[0]+=$timediff;
                    $timediff=0;
                } else {
                    $timediff+=60;
                    $times[0]-=60;
                }
                if ($times[0]<0) {
                    $times[0]+=60;
                    $times[1]--;
                    if ($times[1]<0) {
                        $times[1]+=60;
                        $times[2]--;
                        if ($times[2]<0) {
                            $times[2]+=12;
                            if ($times[3]=="pm"){
                                $times[3]=="am";
                            } else {
                                $times[3]="pm";
                                $times[4]--;
                                if ($times[4]<0) {
                                    if (in_array($times[5], Array("Jan", "Feb","Apr","Jun","Sep", "Nov"))) {
                                        $times[4]+=31;
                                        switch ($times[5]) {
                                            case 'Jan':
                                                $times[5]='Des';
                                                $times[6]--;
                                                break;
                                            case 'Feb':
                                                $times[5]='Jan';
                                                break;
                                            case 'Apr':
                                                $times[5]='Mar';
                                                break;
                                            case 'Jun':
                                                $times[5]='May';
                                                break;
                                            case 'Sep':
                                                $times[5]='Aug';
                                                break;
                                            case 'Nov':
                                                $times[5]='Oct';
                                                break;
                                        }
                                    } elseif (in_array($times[5], Array("May", "Jul", "Oct", "Des"))) {
                                        $times[4]+=30;
                                        switch ($times[5]) {
                                            case 'May':
                                                $times[5]='Apr';
                                                break;
                                            case 'Jul':
                                                $times[5]='Jun';
                                                break;
                                            case 'Oct':
                                                $times[5]='Sep';
                                                break;
                                            case 'Des':
                                                $times[5]='Nov';
                                                break;
                                        }
                                    } elseif ($times[5]==='Mar') {
                                        $times[4]+=28;
                                        if ($times[6]%4===0) {
                                            if (!$times[6]%400===0) {
                                                $times[4]+=1;
                                            }
                                        }
                                        $times[5]='Feb';
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                if ($timediff<60) {
                    $times[0]+=$timediff;
                    $timediff=0;
                    if ($times[0]>=60) {
                        $times[0]-=60;
                        $times[1]++;
                    }
                } else {
                    $timediff-=60;
                    $times[1]++;
                }
                if ($times[1]>=60) {
                    $times[1]-=60;
                    $times[2]++;
                    if ($times[2]>=12) {
                        $times[2]-=12;
                        if ($times[3]=="am"){
                            $times[4]=="pm";
                        } else {
                            $times[3]="am";
                            $times[4]++;
                            //'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Des';
                            if (in_array($times[5], Array('Jan','Mar','May','Jul','Aug','Oct','Des'))) {
                                if ($times[4]>31) {
                                    $times[4]=0;
                                    switch ($times[5]) {
                                        case 'Jan':
                                            $times[5]="Feb";
                                            break;
                                        case 'Mar':
                                            $times[5]="Apr";
                                            break;
                                        case 'May':
                                            $times[5]="Jun";
                                            break;
                                        case 'Jul':
                                            $times[5]="Aug";
                                            break;
                                        case 'Aug':
                                            $times[5]="Sep";
                                            break;
                                        case 'Oct':
                                            $times[5]="Nov";
                                            break;
                                        case 'Des':
                                            $times[5]="Jan";
                                            $times[6]++;
                                            break;
                                    }
                                }
                            } elseif (in_array($times[5], Array('Apr','Jun','Sep','Nov'))) {
                                if ($times[4]>30) {
                                    $times[4]=0;
                                    switch ($times[5]) {
                                        case 'Apr':
                                            $times[5]="May";
                                            break;
                                        case 'Jun':
                                            $times[5]="Jul";
                                            break;
                                        case 'Sep':
                                            $times[5]="Oct";
                                            break;
                                        case 'Nov':
                                            $times[5]="Des";
                                            break;
                                    }
                                }
                            } elseif ($times[5]==="Feb") {
                                if ($times[6]%4===0 and $times[6]%400!==0) {
                                    $num = 29;
                                } else {
                                    $num = 28;
                                }
                                if ($times[4]>$num) {
                                    $times[4]=0;
                                    $times[5]='Mar';
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    $hourangle = $hpd['degrees']*intval($times[2])+$mpd['degrees']/12*intval($times[1]);
    $minangle = $mpd['degrees']*intval($times[1]);
    $secangle = $spd['degrees']*intval($times[0]);

    if ($x) {
        $zoneabbr = get_timezone_abbreviation($ip_info["timezone"]);
    } else {
        $zoneabbr = $clitimezone_abbr;
    }
    imagestring($im, 5, $ellxoffset-$ellwid/2+($width/2)*$x, $elly+$ellwid/2 + 10, "{$times[2]}:{$times[1]}:{$times[0]} {$times[3]}, {$times[4]}. {$times[5]}, {$times[6]} {$zoneabbr}", $dark_gray);

    drawclockarm($im, $ellx, $elly, $hpd['len']*($ellwid/2), $hourangle - 90, $white, $hpd['width']);
    drawclockarm($im, $ellx, $elly, $mpd['len']*($ellwid/2), $minangle - 90, $white, $mpd['width']);
    drawclockarm($im, $ellx, $elly, $spd['len']*($ellwid/2), $secangle - 90, $red, $spd['width']);
}
imagestring($im, 2, 4, $height-14, "https://www.pablodons.tk/dateandtimeimage.php - Created by PabloDons", $red);
header ('Content-Type: image/png');
imagepng($im);
imagedestroy($im);
?>
