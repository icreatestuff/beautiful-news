<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Site URL
 *
 * Create a local URL based on your basepath. Segments can be passed via the
 * first parameter either as a string or an array.
 *
 * @access	public
 * @param	string
 * @return	string
 */
if ( ! function_exists('site_url'))
{
	function site_url($uri = '')
	{
		$CI =& get_instance();
		return $CI->config->site_url($uri);
	}
}

function size($src='', $new_width='', $new_height='', $alt='', $class='', $cache='', $refresh='1440')
{		
	$CI =& get_instance();
	
	$CI->load->helper('string');
	// --------------------------------------------------
	//  Determine base path
	// --------------------------------------------------

	// checks if server supports $ENV DOCUMENT_ROOT use $_SERVER otherwise
	if (array_key_exists('DOCUMENT_ROOT', $_ENV))
	{
		$base_path = $_ENV['DOCUMENT_ROOT']."/";
	}
	else
	{
		$base_path = $_SERVER['DOCUMENT_ROOT']."/";
	}
	
	$base_path = str_replace("\\", "/", $base_path);
	$base_path = reduce_double_slashes($base_path);
		
	// -------------------------------------
	// define some defaults
	// -------------------------------------	
	$img = array();
	$okmime = array("image/png", "image/gif", "image/jpeg");
	
	
	// -------------------------------------
	// collect passed vars from the tag
	// -------------------------------------
	$img['base_path'] = $base_path;
	$img['new_width'] = $new_width;
	$img['new_height'] = $new_height;
	$img['cache'] = $cache;
	$img['alt'] = $alt;
	$img['class'] = $class;
	
	// -------------------------------------
	// if no src thers not much we can do..
	// -------------------------------------
	if (!$src)
	{
		 return false;
	}

	// -------------------------------------
	// die if user trys to pass in a img tag
	// -------------------------------------
	if (stristr($src, '<img'))
	{
		return false;			
	}

	// -------------------------------------
	// extract necessities from full URL
	// -------------------------------------	
	if (stristr($src, 'https'))
	{
		$img['url_src'] = $src; // save the URL src for remote
		$urlarray = parse_url($img['url_src']);
		$img['url_host_cache_dir'] =  str_replace('.', '-', $urlarray['host']);			
		$src = '/'.$urlarray['path'];
	}
				
	$img['src'] = reduce_double_slashes("/" . $src);
	$img['base_path'] = reduce_double_slashes($base_path);
	
	$img_full_path = reduce_double_slashes($img['base_path'].$img['src']);

	// can we actually read this file?
	if (!is_readable($img_full_path))
	{	
		return false;
	}
	
	
	// -------------------------------------
	// get src img sizes and mime type
	// -------------------------------------
	$size = getimagesize($img_full_path);
	$img['src_width'] = $size[0];
	$img['src_height'] = $size[1];      
	$img['mime'] = $size['mime'];
		
		
	// -------------------------------------
	// get src relitive path
	// -------------------------------------	
	$src_pathinfo = pathinfo($img['src']);
	$img['base_rel_path'] = reduce_double_slashes($src_pathinfo['dirname'] . "/");	

	// -------------------------------------
	// define all the file location pointers we will need 
	// -------------------------------------	
	$img_full_pathinfo = pathinfo($img_full_path);			
	$img['root_path'] = ( ! isset($img_full_pathinfo['dirname'])) ? '' : reduce_double_slashes($img_full_pathinfo['dirname'] . "/");
	$img['basename'] = ( ! isset($img_full_pathinfo['basename'])) ? '' : $img_full_pathinfo['basename'];
	$img['extension'] = ( ! isset($img_full_pathinfo['extension'])) ? '' : $img_full_pathinfo['extension'];
	$img['base_filename'] = str_replace("." . $img['extension'], "", $img_full_pathinfo['basename']);
	
	// -------------------------------------
	// lets stop this if the image is not in the ok mime types 
	// -------------------------------------
	if (!in_array($img['mime'], $okmime))
	{
		return false;
	}
			

	// -------------------------------------
	// build cache location
	// -------------------------------------
	$base_cache = reduce_double_slashes($base_path . "/images/sized/");
	$base_cache = reduce_double_slashes($base_cache);
	
	$img['cache_path'] = reduce_double_slashes($base_cache . $img['base_rel_path']);
	
	if (!is_dir($img['cache_path']))
	{
		// make the directory if we can 
		if (!mkdir($img['cache_path'], 0777, true))
		{			
			return false;
		}
	}
	
	// check if we can put files in the cache directory 
	if (!is_writable($img['cache_path']))
	{
		return false;
	}

	// -------------------------------------
	// do the sizing math
	// -------------------------------------		
	$img = get_some_sizes($img);
	
	// -------------------------------------
	// check the cache
	// -------------------------------------		
	$img = check_some_cache($img);
	
	// -------------------------------------
	// do the sizing if needed 
	// -------------------------------------
	$img = do_some_image($img);

	// -------------------------------------
	// do the output
	// ------------------------------------- 
	return do_output($img);
}


function do_output($img)
{
	$alt = ( ! $img['alt']) ? '' : $img['alt'];
	$style = '';
	$class = ( ! $img['class']) ? '' : $img['class'];
	$title = '';
	$id = '';
	$justurl = '';
	$server_domain = '';
	
	$browser_out_path = $server_domain . $img['browser_out_path'];
	$img['browser_out_path'] = reduce_double_slashes($browser_out_path);
	
	
	/** -------------------------------------
	/*  sometimes we may just want the path to the image e.g. RSS feeds
	/** -------------------------------------*/
	if ($justurl)
	{
		return $img['browser_out_path'];
	}		

	/** -------------------------------------
	/*  this is the default output just a simpe img tag 
	/** -------------------------------------*/
	$out_tag = "<img src=\"".$img['browser_out_path']."\" width=\"".$img['out_width']."\" height=\"".$img['out_height']."\" ";
	
	$out_tag .= ($id ? " id=\"$id\"" : "");
	
	$out_tag .= ($title ? " title=\"$title\"" : "");
	
	$out_tag .= ($alt ? " alt=\"$alt\"" : " alt=\"\"");

	$out_tag .= ($class ? " class=\"$class\"" : "");
	
	$out_tag .= ($style ? " style=\"$style\"" : "");
					
	return $out_tag." />";	
}


// -------------------------------------
// checks cached images if they are present
// and if they are older than the src img
// -------------------------------------

function check_some_cache($img)
{
	$img['do_cache'] = "";
	
	$cache = ( ! $img['cache']) ? '' : $img['cache'];
	
	$imageModified = @filemtime($img['base_path'] . $img['src']);
	$thumbModified = @filemtime($img['cache_path'] . $img['out_name']);

    // set a update flag 
	if ($imageModified > $thumbModified || $cache == "no")
	{
		$img['do_cache'] = "update";
	}	
	
	return $img;
}


// -------------------------------------
// This function calculates how the image should be resized / cropped etc.
// -------------------------------------

function get_some_sizes($img)
{
	// set some defaults
	$width = $img['src_width'];
	$height = $img['src_height'];
	$img['crop'] = "";
	$img['proportional'] = true;
	$color_space = "";

	$auto = '';
	$max_width = ( ! $img['new_width']) ? '9000' : $img['new_width']; 
	$max_height = ( ! $img['new_height']) ? '9000' : $img['new_height'];
	$greyscale = '';
	
	
	if ($greyscale)
	{
		$color_space = "-greyscale";		
	}		
		
	// -------------------------------------
	// get the ratio needed
	// -------------------------------------
	$x_ratio = $max_width / $width;
	$y_ratio = $max_height / $height;
	
	// -------------------------------------
	// if image already meets criteria, load current values in
	// if not, use ratios to load new size info
	// -------------------------------------
	if (($width <= $max_width) && ($height <= $max_height) )
	{
		$img['out_width'] = $width;
		$img['out_height'] = $height;
	} 
	else if (($x_ratio * $height) < $max_height) 
	{
		$img['out_height'] = ceil($x_ratio * $height);
		$img['out_width'] = $max_width;
	} 
	else 
	{
		$img['out_width'] = ceil($y_ratio * $width);
		$img['out_height'] = $max_height;
	}
	
	
	// -------------------------------------
	//  Set image sizing Ratio
	//  Auto size Added By Erin Dalzell 
	// -------------------------------------
	if ($auto)
	{
		if ($width > $height)
		{
			$img['out_width'] = $auto;
			$img['out_height'] = '0';
		}
		else
		{
			$img['out_height'] = $auto;
			$img['out_width'] = '0';
		}
	}		

	// -------------------------------------
	// Do we want to Crop the image?
	// -------------------------------------
	if ($max_height != '9000' && $max_width != '9000' && $max_width != $max_height)
	{
		$img['crop'] = "yes";
		$img['proportional'] = false;
		$img['out_width'] = $max_width;
		$img['out_height'] = $max_height;
	}

	// -------------------------------------
	// Do we Need to crop?
	// -------------------------------------
	if ($max_width == $max_height && $auto == "")
	{
		$img['crop'] = "yes";
		$img['proportional'] = false;
		$img['out_width'] = $max_width;
		$img['out_height'] = $max_height; 
	}
	
	// set outputs 
	$img['out_name'] = $img['base_filename'].$color_space.'-'.$img['out_width'].'x'.$img['out_height'].'.'.$img['extension'];
	$img['root_out_name'] = $img['cache_path'].$img['out_name'];
	$img['browser_out_path'] = reduce_double_slashes("/" . str_replace($img['base_path'], '', $img['root_out_name']));
	
	return $img;
}


// -------------------------------------
// This function does the image resizing
// -------------------------------------
function do_some_image($img) 
{

	$file = $img['root_path'] . $img['basename'];
	
	$width = $img['out_width'];
	$height = $img['out_height'];
	$crop = $img['crop'];	
	$proportional = $img['proportional'];
	$output = $img['cache_path'].$img['out_name'];
	
	$quality = "100";
	$greyscale = '';
    
	if ( $height <= 0 && $width <= 0 ) 
	{
        return false;
    }
   
	$info = getimagesize($file);
	$image = '';
   
	$final_width = 0;
    $final_height = 0;
    list($width_old, $height_old) = $info;

    if ($proportional) 
	{
        if ($width == 0) $factor = $height / $height_old;
        elseif ($height == 0) $factor = $width / $width_old;
        else $factor = min ( $width / $width_old, $height / $height_old);  
       
		$final_width = round ($width_old * $factor);
		$final_height = round ($height_old * $factor);

    }
    else 
    {
		$final_width = ( $width <= 0 ) ? $width_old : $width;
		$final_height = ( $height <= 0 ) ? $height_old : $height;
    }
		
	if ($crop) {
		$int_width = 0;
		$int_height = 0;
		
		$adjusted_height = $final_height;
		$adjusted_width = $final_width;
		
		$wm = $width_old / $width;
		$hm = $height_old / $height;
		$h_height = $height / 2;
		$w_height = $width / 2;
		
		$ratio = $width / $height;
		$old_img_ratio = $width_old / $height_old;
				
		if ($old_img_ratio > $ratio) 
		{
			$adjusted_width = $width_old / $hm;
			$half_width = $adjusted_width / 2;
			$int_width = $half_width - $w_height;
		} 
		else if($old_img_ratio <= $ratio) 
		{
			$adjusted_height = $height_old / $wm;
			$half_height = $adjusted_height / 2;
			$int_height = $half_height - $h_height;
		}
	}


	if ($img['do_cache'])
	{
		@ini_set("memory_limit","12M");
		@ini_set("memory_limit","16M");
		@ini_set("memory_limit","32M");
		@ini_set("memory_limit","64M");			
		
		switch ($info[2]) 
		{
			case IMAGETYPE_GIF:
				$image = imagecreatefromgif($file);
			break;
			case IMAGETYPE_JPEG:
				$image = imagecreatefromjpeg($file);
			break;
			case IMAGETYPE_PNG:
				$image = imagecreatefrompng($file);
			break;
			default:
				return false;
		}

		$image_resized = imagecreatetruecolor($final_width, $final_height);
			
		if ( ($info[2] == IMAGETYPE_GIF) || ($info[2] == IMAGETYPE_PNG) ) 
		{
			$trnprt_indx = imagecolortransparent($image);
  
			// If we have a specific transparent color
			if ($trnprt_indx >= 0) 
			{
				// Get the original image's transparent color's RGB values
				$trnprt_color    = imagecolorsforindex($image, $trnprt_indx);
  
				// Allocate the same color in the new image resource
				$trnprt_indx    = imagecolorallocate($image_resized, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
  
				// Completely fill the background of the new image with allocated color.
				imagefill($image_resized, 0, 0, $trnprt_indx);
  
				// Set the background color for new image to transparent
				imagecolortransparent($image_resized, $trnprt_indx);
			}
			// Always make a transparent background color for PNGs that don't have one allocated already
			elseif ($info[2] == IMAGETYPE_PNG) 
			{
				// Turn off transparency blending (temporarily)
				imagealphablending($image_resized, false);
  
				// Create a new transparent color for image
				$color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
  
				// Completely fill the background of the new image with allocated color.
				imagefill($image_resized, 0, 0, $color);
  
				// Restore transparency blending
				imagesavealpha($image_resized, true);
			}
		}
		

		if ($crop) 
		{   
			imagecopyresampled($image_resized, $image, -$int_width, -$int_height, 0, 0, $adjusted_width, $adjusted_height, $width_old, $height_old);    
		}
		else
		{
			imagecopyresampled($image_resized, $image, 0, 0, 0, 0, $final_width, $final_height, $width_old, $height_old);
		}  

		if ($greyscale)
		{
			for ($c=0; $c<256; $c++)
			{
				$palette[$c] = imagecolorallocate($image_resized,$c,$c,$c);
			}
			
			for ($y=0; $y<$final_height; $y++)
			{
				for ($x=0; $x<$final_width; $x++)
				{
					$rgb = imagecolorat($image_resized,$x,$y);
					$r = ($rgb >> 16) & 0xFF;
					$g = ($rgb >> 8) & 0xFF;
					$b = $rgb & 0xFF;
					$gs = $this->yiq($r,$g,$b);
					imagesetpixel($image_resized,$x,$y,$palette[$gs]);
				}
			} 		
		}
		
		
		switch ($info[2] ) 
		{
			case IMAGETYPE_GIF:
				imagegif($image_resized, $output);
			break;
			case IMAGETYPE_JPEG:
				imagejpeg($image_resized, $output, $quality);
				
			break;
			case IMAGETYPE_PNG:
				imagepng($image_resized, $output);
			break;
			default:
				return false;
		}
	}	
		
	$img['out_width'] =	$final_width;
	$img['out_height'] = $final_height;
				
   return $img;
}


function file_put_contents_atomic($filename, $content) 
{
    $temp = tempnam(FILE_PUT_CONTENTS_ATOMIC_TEMP, 'temp');
    
    if (!($f = @fopen($temp, 'wb'))) 
    {
        $temp = FILE_PUT_CONTENTS_ATOMIC_TEMP . DIRECTORY_SEPARATOR . uniqid('temp');
        if (!($f = @fopen($temp, 'wb'))) 
        {
            trigger_error("file_put_contents_atomic() : error writing temporary file '$temp'", E_USER_WARNING);
            return false;
        }
    }
  
    fwrite($f, $content);
    fclose($f);
  
    if (!@rename($temp, $filename)) 
    {
        @unlink($filename);
        @rename($temp, $filename);
    }
  
    @chmod($filename, FILE_PUT_CONTENTS_ATOMIC_MODE);
  
    return true;
} 

//Creates yiq function
function yiq($r,$g,$b)
{
	return (($r*0.299)+($g*0.587)+($b*0.114));
}	
/* End of file url_helper.php */
/* Location: ./system/helpers/url_helper.php */