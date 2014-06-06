<?php

/**
 * Convert unicode in json to utf-8 chars
 *
 * @param string $json String to convert
 * @return string utf-8 string
 */
function json_unicode_to_utf8($json){
	$json = preg_replace_callback("/\\\\u([0-9a-f]{4})/", create_function('$match', '
		$val = intval($match[1], 16);
		$c = "";
		if($val < 0x7F){        // 0000-007F
			$c .= chr($val);
		} elseif ($val < 0x800) { // 0080-0800
			$c .= chr(0xC0 | ($val / 64));
			$c .= chr(0x80 | ($val % 64));
		} else {                // 0800-FFFF
			$c .= chr(0xE0 | (($val / 64) / 64));
			$c .= chr(0x80 | (($val / 64) % 64));
			$c .= chr(0x80 | ($val % 64));
		}
		return $c;
	'), $json);
	return $json;
}

/**
 * Format JSON to pretty html
 *
 * @param string $json JSON to format
 * @return string
 */
function json_format_html($json)
{
	$json = json_unicode_to_utf8($json);
    $tab = "&nbsp;&nbsp;";
    $new_json = "";
    $indent_level = 0;
    $in_string = false;

    $len = strlen($json);

    for($c = 0; $c < $len; $c++)
    {
        $char = $json[$c];
        switch($char)
        {
            case '{':
            case '[':
            	$char = "<font color=\"green\">" . $char . "</font>";//iwind
                if(!$in_string) {
                    $new_json .= $char . "<br/>" . str_repeat($tab, $indent_level+1);
                    $indent_level++;
                }
                else {
                    $new_json .= "[";
                }
                break;
            case '}':
            case ']':
            	$char = "<font color=\"green\">" . $char . "</font>";//iwind
                if(!$in_string)
                {
                    $indent_level--;
                    $new_json .= "<br/>" . str_repeat($tab, $indent_level) . $char;
                }
                else
                {
                    $new_json .= "]";
                }
                break;
            case ',':
            	$char = "<font color=\"green\">" . $char . "</font>";//iwind
                if(!$in_string) {
                    $new_json .= ",<br/>" . str_repeat($tab, $indent_level);
                }
                else {
                    $new_json .= ",";
                }
                break;
            case ':':
            	$char = "<font color=\"green\">" . $char . "</font>";//iwind
                if($in_string) {
                    $new_json .= ":";
                }
                else {
                    $new_json .= $char;
                }
                break;
            case '"':
                if($c > 0 && $json[$c-1] != '\\') {
                    $in_string = !$in_string;
                    if ($in_string) {
                    	$new_json .= "<font color=\"#DD0000\" class=\"string_var\">" . $char;
                    }
                    else {
                    	$new_json .= $char . "</font>";
                    }
       				break;
                }
                else if ($c == 0) {
                	$in_string = !$in_string;
                	$new_json .= "<font color=\"red\">" . $char;
                	break;
                }
            default:
            	if (!$in_string && trim($char) !== "") {
            		$char = "<font color=\"blue\">" . $char . "</font>";
            	}
            	else {
            		if ($char == "&" || $char == "'" || $char == "\"" || $char == "<" || $char == ">") {
            			$char = htmlspecialchars($char);
            		}
            	}
                $new_json .= $char;
                break;
        }
    }
    $new_json = preg_replace_callback("{(<font color=\"blue\">([\\da-zA-Z_\\.]+)</font>)+}", create_function('$match','
    	$string = str_replace("<font color=\"blue\">", "", $match[0]);
    	$string = str_replace("</font>", "", $string);
    	return "<font color=\"blue\" class=\"no_string_var\">" . $string  . "</font>";
    '), $new_json);
    return $new_json;
}


/**
 * PHP Integration of Open Flash Chart
 * Copyright (C) 2008 John Glazebrook <open-flash-chart@teethgrinder.co.uk>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 */


/**
 * Format JSON to pretty style
 *
 * @param string $json JSON to format
 * @return string
 */
function json_format($json)
{
    $tab = "  ";
    $new_json = "";
    $indent_level = 0;
    $in_string = false;

/*
 commented out by monk.e.boy 22nd May '08
 because my web server is PHP4, and
 json_* are PHP5 functions...

    $json_obj = json_decode($json);

    if($json_obj === false)
        return false;

    $json = json_encode($json_obj);
*/
    $len = strlen($json);

    for($c = 0; $c < $len; $c++)
    {
        $char = $json[$c];
        switch($char)
        {
            case '{':
            case '[':
                if(!$in_string)
                {
                    $new_json .= $char . "\n" . str_repeat($tab, $indent_level+1);
                    $indent_level++;
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case '}':
            case ']':
                if(!$in_string)
                {
                    $indent_level--;
                    $new_json .= "\n" . str_repeat($tab, $indent_level) . $char;
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case ',':
                if(!$in_string)
                {
                    $new_json .= ",\n" . str_repeat($tab, $indent_level);
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case ':':
                if(!$in_string)
                {
                    $new_json .= ": ";
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case '"':
                if($c > 0 && $json[$c-1] != '\\')
                {
                    $in_string = !$in_string;
                }
            default:
                $new_json .= $char;
                break;
        }
    }

    return $new_json;
}

/**
 * Format bytes to human size
 *
 * @param integer $bytes Size in byte
 * @param integer $precision Precision
 * @return string size in k, m, g..
 * @since 1.1.7
 */
function r_human_bytes($bytes, $precision = 2) {
	if ($bytes == 0) {
		return 0;
	}
	if ($bytes < 1024) {
		return $bytes . "B";
	}
	if ($bytes < 1024 * 1024) {
		return round($bytes/1024, $precision) . "k";
	}
	if ($bytes < 1024 * 1024 * 1024) {
		return round($bytes/1024/1024, $precision) . "m";
	}
	if ($bytes < 1024 * 1024 * 1024 * 1024) {
		return round($bytes/1024/1024/1024, $precision) . "g";
	}
	return $bytes;
}

/**
 * Get collection display icon
 *
 * @param string $collectionName Collection name
 * @return string
 * @since 1.1.8
 */
function r_get_collection_icon($collectionName) {
	if (preg_match("/\\.(files|chunks)$/", $collectionName)){
		return "grid";
	}
	if (preg_match("/^system\\.js$/", $collectionName)) {
		return "table-systemjs";
	}
	return "table";
}

?>