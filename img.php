<?php 

$imgsrc = "bmp.bmp";
$newWidth = 200;
$newHeight = 200;

resizeImage($imgsrc,$newWidth,$newHeight);
function resizeImage($imgsrc,$imgWidth,$imgHeight) {

	$type = strtolower(substr(strrchr($imgsrc,"."),1));
	if($type == 'jpeg') $type = 'jpg';
	switch($type){
	    case 'bmp': $oldImage = imagecreatefrombmp($imgsrc);break;
	    case 'gif': $oldImage = imagecreatefromgif($imgsrc);break;
	    case 'jpg': $oldImage = imagecreatefromjpeg($imgsrc);break;
	    case 'png': $oldImage = imagecreatefrompng($imgsrc);break;
	    default :
	        echo "Unsupported picture type!";
	        exit;
	}
	//取得图片的宽度,高度值
	$arr = getimagesize($imgsrc);

	$newImage = imagecreatetruecolor($imgWidth, $imgHeight); //创建一个彩色的底图
	imagecopyresampled($newImage, $oldImage, 0, 0, 0, 0,$imgWidth,$imgHeight,$arr[0], $arr[1]);

	switch($type){
	    case 'bmp': header('Content-Type:image/wbmp');imagebmp($newImage); break;
	    case 'gif': header('Content-Type:image/gif');imagegif($newImage); break;
	    case 'jpg': header('Content-Type:image/jpg');imagejpeg($newImage); break;
	    case 'png': header('Content-Type:image/png');imagepng($newImage); break;
	    default:
	        echo "Unsupported picture type!";
	        exit;
	}
	imagedestroy($newImage);
}




/**
 * BMP 创建函数 (blue 在网上找的代码)
 * @author simon
 * @param string $filename path of bmp file
 * @example who use,who knows
 * @return resource of GD
 */
function imagecreatefrombmp( $filename ){
    if ( !$f1 = fopen( $filename, "rb" ) )
        return FALSE;
    $FILE = unpack( "vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread( $f1, 14 ) );
    if ( $FILE['file_type'] != 19778 )
        return FALSE;
    $BMP = unpack( 'Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel' . '/Vcompression/Vsize_bitmap/Vhoriz_resolution' . '/Vvert_resolution/Vcolors_used/Vcolors_important', fread( $f1, 40 ) );
    $BMP['colors'] = pow( 2, $BMP['bits_per_pixel'] );
    if ( $BMP['size_bitmap'] == 0 )
        $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];
    $BMP['bytes_per_pixel'] = $BMP['bits_per_pixel'] / 8;
    $BMP['bytes_per_pixel2'] = ceil( $BMP['bytes_per_pixel'] );
    $BMP['decal'] = ($BMP['width'] * $BMP['bytes_per_pixel'] / 4);
    $BMP['decal'] -= floor( $BMP['width'] * $BMP['bytes_per_pixel'] / 4 );
    $BMP['decal'] = 4 - (4 * $BMP['decal']);
    if ( $BMP['decal'] == 4 )
        $BMP['decal'] = 0;
    $PALETTE = array();
    if ( $BMP['colors'] < 16777216 ){
        $PALETTE = unpack( 'V' . $BMP['colors'], fread( $f1, $BMP['colors'] * 4 ) );
    }
    $IMG = fread( $f1, $BMP['size_bitmap'] );
    $VIDE = chr( 0 );
    $res = imagecreatetruecolor( $BMP['width'], $BMP['height'] );
    $P = 0;
    $Y = $BMP['height'] - 1;
    while( $Y >= 0 ){
        $X = 0;
        while( $X < $BMP['width'] ){
            if ( $BMP['bits_per_pixel'] == 32 ){
                $COLOR = unpack( "V", substr( $IMG, $P, 3 ) );
                $B = ord(substr($IMG, $P,1));
                $G = ord(substr($IMG, $P+1,1));
                $R = ord(substr($IMG, $P+2,1));
                $color = imagecolorexact( $res, $R, $G, $B );
                if ( $color == -1 )
                    $color = imagecolorallocate( $res, $R, $G, $B );
                $COLOR[0] = $R*256*256+$G*256+$B;
                $COLOR[1] = $color;
            }elseif ( $BMP['bits_per_pixel'] == 24 )
                $COLOR = unpack( "V", substr( $IMG, $P, 3 ) . $VIDE );
            elseif ( $BMP['bits_per_pixel'] == 16 ){
                $COLOR = unpack( "n", substr( $IMG, $P, 2 ) );
                $COLOR[1] = $PALETTE[$COLOR[1] + 1];
            }elseif ( $BMP['bits_per_pixel'] == 8 ){
                $COLOR = unpack( "n", $VIDE . substr( $IMG, $P, 1 ) );
                $COLOR[1] = $PALETTE[$COLOR[1] + 1];
            }elseif ( $BMP['bits_per_pixel'] == 4 ){
                $COLOR = unpack( "n", $VIDE . substr( $IMG, floor( $P ), 1 ) );
                if ( ($P * 2) % 2 == 0 )
                    $COLOR[1] = ($COLOR[1] >> 4);
                else
                    $COLOR[1] = ($COLOR[1] & 0x0F);
                $COLOR[1] = $PALETTE[$COLOR[1] + 1];
            }elseif ( $BMP['bits_per_pixel'] == 1 ){
                $COLOR = unpack( "n", $VIDE . substr( $IMG, floor( $P ), 1 ) );
                if ( ($P * 8) % 8 == 0 )
                    $COLOR[1] = $COLOR[1] >> 7;
                elseif ( ($P * 8) % 8 == 1 )
                    $COLOR[1] = ($COLOR[1] & 0x40) >> 6;
                elseif ( ($P * 8) % 8 == 2 )
                    $COLOR[1] = ($COLOR[1] & 0x20) >> 5;
                elseif ( ($P * 8) % 8 == 3 )
                    $COLOR[1] = ($COLOR[1] & 0x10) >> 4;
                elseif ( ($P * 8) % 8 == 4 )
                    $COLOR[1] = ($COLOR[1] & 0x8) >> 3;
                elseif ( ($P * 8) % 8 == 5 )
                    $COLOR[1] = ($COLOR[1] & 0x4) >> 2;
                elseif ( ($P * 8) % 8 == 6 )
                    $COLOR[1] = ($COLOR[1] & 0x2) >> 1;
                elseif ( ($P * 8) % 8 == 7 )
                    $COLOR[1] = ($COLOR[1] & 0x1);
                $COLOR[1] = $PALETTE[$COLOR[1] + 1];
            }else
                return FALSE;
            imagesetpixel( $res, $X, $Y, $COLOR[1] );
            $X++;
            $P += $BMP['bytes_per_pixel'];
        }
        $Y--;
        $P += $BMP['decal'];
    }
    fclose( $f1 );
    return $res;
}
/**
 * 创建bmp格式图片 (blue 在网上找的代码)
 *
 * @author: legend(legendsky@hotmail.com)
 * @link: http://www.ugia.cn/?p=96
 * @description: create Bitmap-File with GD library
 * @version: 0.1
 *
 * @param resource $im          图像资源
 * @param string   $filename    如果要另存为文件，请指定文件名，为空则直接在浏览器输出
 * @param integer  $bit         图像质量(1、4、8、16、24、32位)
 * @param integer  $compression 压缩方式，0为不压缩，1使用RLE8压缩算法进行压缩
 *
 * @return integer
 */
function imagebmp(&$im, $filename = '', $bit = 8, $compression = 0)
{
    if (!in_array($bit, array(1, 4, 8, 16, 24, 32)))
    {
        $bit = 8;
    }
    else if ($bit == 32) // todo:32 bit
    {
        $bit = 24;
    }
    $bits = pow(2, $bit);
    // 调整调色板
    imagetruecolortopalette($im, true, $bits);
    $width  = imagesx($im);
    $height = imagesy($im);
    $colors_num = imagecolorstotal($im);
    if ($bit <= 8)
    {
        // 颜色索引
        $rgb_quad = '';
        for ($i = 0; $i < $colors_num; $i ++)
        {
            $colors = imagecolorsforindex($im, $i);
            $rgb_quad .= chr($colors['blue']) . chr($colors['green']) . chr($colors['red']) . "\0";
        }
        // 位图数据
        $bmp_data = '';
        // 非压缩
        if ($compression == 0 || $bit < 8)
        {
            if (!in_array($bit, array(1, 4, 8)))
            {
                $bit = 8;
            }
            $compression = 0;
            // 每行字节数必须为4的倍数，补齐。
            $extra = '';
            $padding = 4 - ceil($width / (8 / $bit)) % 4;
            if ($padding % 4 != 0)
            {
                $extra = str_repeat("\0", $padding);
            }
            for ($j = $height - 1; $j >= 0; $j --)
            {
                $i = 0;
                while ($i < $width)
                {
                    $bin = 0;
                    $limit = $width - $i < 8 / $bit ? (8 / $bit - $width + $i) * $bit : 0;
                    for ($k = 8 - $bit; $k >= $limit; $k -= $bit)
                    {
                        $index = imagecolorat($im, $i, $j);
                        $bin |= $index << $k;
                        $i ++;
                    }
                    $bmp_data .= chr($bin);
                }
                $bmp_data .= $extra;
            }
        }
        // RLE8 压缩
        else if ($compression == 1 && $bit == 8)
        {
            for ($j = $height - 1; $j >= 0; $j --)
            {
                $last_index = "\0";
                $same_num   = 0;
                for ($i = 0; $i <= $width; $i ++)
                {
                    $index = imagecolorat($im, $i, $j);
                    if ($index !== $last_index || $same_num > 255)
                    {
                        if ($same_num != 0)
                        {
                            $bmp_data .= chr($same_num) . chr($last_index);
                        }
                        $last_index = $index;
                        $same_num = 1;
                    }
                    else
                    {
                        $same_num ++;
                    }
                }
                $bmp_data .= "\0\0";
            }
            $bmp_data .= "\0\1";
        }
        $size_quad = strlen($rgb_quad);
        $size_data = strlen($bmp_data);
    }
    else
    {
        // 每行字节数必须为4的倍数，补齐。
        $extra = '';
        $padding = 4 - ($width * ($bit / 8)) % 4;
        if ($padding % 4 != 0)
        {
            $extra = str_repeat("\0", $padding);
        }
        // 位图数据
        $bmp_data = '';
        for ($j = $height - 1; $j >= 0; $j --)
        {
            for ($i = 0; $i < $width; $i ++)
            {
                $index  = imagecolorat($im, $i, $j);
                $colors = imagecolorsforindex($im, $index);
                if ($bit == 16)
                {
                    $bin = 0 << $bit;
                    $bin |= ($colors['red'] >> 3) << 10;
                    $bin |= ($colors['green'] >> 3) << 5;
                    $bin |= $colors['blue'] >> 3;
                    $bmp_data .= pack("v", $bin);
                }
                else
                {
                    $bmp_data .= pack("c*", $colors['blue'], $colors['green'], $colors['red']);
                }
                // todo: 32bit;
            }
            $bmp_data .= $extra;
        }
        $size_quad = 0;
        $size_data = strlen($bmp_data);
        $colors_num = 0;
    }
    // 位图文件头
    $file_header = "BM" . pack("V3", 54 + $size_quad + $size_data, 0, 54 + $size_quad);
    // 位图信息头
    $info_header = pack("V3v2V*", 0x28, $width, $height, 1, $bit, $compression, $size_data, 0, 0, $colors_num, 0);
    // 写入文件
    if ($filename != '')
    {
        $fp = fopen("test.bmp", "wb");
        fwrite($fp, $file_header);
        fwrite($fp, $info_header);
        fwrite($fp, $rgb_quad);
        fwrite($fp, $bmp_data);
        fclose($fp);
        return 1;
    }
    // 浏览器输出
    header("Content-Type: image/bmp");
    echo $file_header . $info_header;
    echo $rgb_quad;
    echo $bmp_data;
    return 1;
}

?>