<?php

/* * *****************************************************************************
 *
 *  MAKE ME SPRITE
 *
 *  LICENSE (MIT):
 *
 *  Copyright (c) 2011 Cezary Srednicki <cezary@srednicki.info>
 *
 *  Permission is hereby granted, free of charge, to any person
 *  obtaining a copy of this software and associated documentation
 *  files (the "Software"), to deal in the Software without
 *  restriction, including without limitation the rights to use,
 *  copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the
 *  Software is furnished to do so, subject to the following
 *  conditions:
 *
 *  The above copyright notice and this permission notice shall be
 *  included in all copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 *  EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 *  OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 *  NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 *  HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 *  WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 *  FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 *  OTHER DEALINGS IN THE SOFTWARE.
 *
 * ***************************************************************************** */

error_reporting(E_ERROR | E_WARNING | E_PARSE);
define('nl', "\r\n");
define('MSG_OFF', 0);
define('MSG_IMPORTANT', 1);
define('MSG_NORMAL', 2);
define('MSG_MORE', 3);
define('MSG_DEBUG', 4);

/**
 * Parses $GLOBALS['argv'] for parameters and assigns them to an array.
 *
 * Supports:
 * -e
 * -e <value>
 * --long-param
 * --long-param=<value>
 * --long-param <value>
 * <value>
 *
 * @param array $noopt List of parameters without values
 */
function parseParameters($noopt = array()) {
    $result = array();
    $params = $GLOBALS['argv'];
    // could use getopt() here (since PHP 5.3.0), but it doesn't work relyingly
    reset($params);
    while (list($tmp, $p) = each($params)) {
        if ($p{0} == '-') {
            $pname = substr($p, 1);
            $value = true;
            if ($pname{0} == '-') {
                // long-opt (--<param>)
                $pname = substr($pname, 1);
                if (strpos($p, '=') !== false) {
                    // value specified inline (--<param>=<value>)
                    list($pname, $value) = explode('=', substr($p, 2), 2);
                }
            }
            // check if next parameter is a descriptor or a value
            $nextparm = current($params);
            if (!in_array($pname, $noopt) && $value === true && $nextparm !== false && $nextparm{0} != '-')
                list($tmp, $value) = each($params);
            $result[$pname] = $value;
        } else {
            // param doesn't belong to any option
            $result[] = $p;
        }
    }
    return $result;
}

function msg($txt, $priority=MSG_NORMAL) {
    if (VERBOSE_LEVEL >= $priority) {
        return $txt;
    }
}

function read_config() {
    $path_parts = pathinfo(CONFIGURATION_FILE);
    if (file_exists(CONFIGURATION_FILE)) {
        $config_file = file(CONFIGURATION_FILE);
        echo msg('Reading config file ' . CONFIGURATION_FILE . nl, MSG_NORMAL);
        $i = 1;
        foreach ($config_file AS $config_line) {
            $line_number = '#' . $i . ': ';
            $line_trimmed = trim($config_line);
            if (isset($line_trimmed) && !empty($line_trimmed)) {
                // jezeli pierwszy znak to ; to mamy do czynienia z linia komentarza
                if (strpos($line_trimmed, ';', 0) !== 0) {
                    echo msg($line_number, MSG_DEBUG);
                    if ($line_vars = explode(',', $line_trimmed)) {
                        if (isset($line_vars[0]) && !empty($line_vars[0])) {
                            $image_name = '.' . file_name($line_trimmed);
                            $image_file = $line_vars[0];
                            if (isset($line_vars[1]) && !empty($line_vars[1])) {
                                $image_name = $line_vars[0];
                                $image_file = $line_vars[1];
                            }
                        } else {
                            echo msg('ERROR: Failed at line number ' . $i . nl, MSG_IMPORTANT);
                            echo msg($line_trimmed, MSG_IMPORTANT);
                            return false;
                        }
                    }
                    echo msg($image_name . ' => ', MSG_DEBUG);
                    $file_path = implode('/', array($path_parts['dirname'], $image_file));
                    echo msg($file_path . ' ', MSG_DEBUG);
                    if (file_exists($file_path)) {
                        $file_array[$image_name]['path'] = $file_path;
                        echo msg('Done' . nl, MSG_DEBUG);
                        if ($image_size = getimagesize($file_path)) {
                            $file_array[$image_name]['x'] = $image_size[0];
                            $file_array[$image_name]['y'] = $image_size[1];
                        } else {
                            echo msg('ERROR: Failed to get image size ' . $file_path . nl, MSG_IMPORTANT);
                            return false;
                        }
                    } else {
                        echo msg('ERROR: File not found ' . $file_path . nl, MSG_IMPORTANT);
                        return false;
                    }
                }
                $i++;
            }
        }
        return $file_array;
    } else {
        echo msg('ERROR: File not found ' . CONFIGURATION_FILE . nl, MSG_IMPORTANT);
        return false;
    }
}

function arrange_optimal($config_array) {

    echo msg('Calculating sprites positions ... ', MSG_MORE);
    foreach ($config_array AS $sprite_config_key => $sprite_config_val) {
        $width_array[$sprite_config_key] = $sprite_config_val['x'] + (2 * IMAGE_BORDERS);
        $height_array[$sprite_config_key] = $sprite_config_val['y'] + (2 * IMAGE_BORDERS);
    }
    $width_min = max($width_array);

    function custom_sort($a, $b) {
        return ($a == $b) ? 0 : ($a < $b) ? 1 : -1;
    }

    uasort($height_array, "custom_sort");
    $pos_x = IMAGE_BORDERS;
    $pos_y = IMAGE_BORDERS;
    $max_line_height[] = 0;

    foreach ($height_array AS $sprite_config_key => $sprite_config_val) {
        $sprite_config = $config_array[$sprite_config_key];
        if ($pos_x + IMAGE_BORDERS + $sprite_config['x'] > $width_min) {
            $pos_x = IMAGE_BORDERS;
            $pos_y = $pos_y + IMAGE_BORDERS + max($max_line_height);
            unset($max_line_height);
        }
        $placement_array[$sprite_config_key] = array(
            'x' => $pos_x,
            'y' => $pos_y,
            'width' => $sprite_config['x'],
            'height' => $sprite_config['y'],
            'path' => $sprite_config['path']
        );
        $pos_x = $pos_x + IMAGE_BORDERS + $sprite_config['x'] + IMAGE_BORDERS;
        $max_line_height[] = IMAGE_BORDERS + $sprite_config['y'];
    }

    echo msg('Done' . nl, MSG_MORE);
    return array(
        'canvas' => array(
            'width' => $width_min,
            'height' => $pos_y + IMAGE_BORDERS + max($max_line_height),
        ),
        'sprites' => $placement_array
    );
}

function arrange_by_rows($config_array) {

    echo msg('Calculating sprites positions by rows', MSG_MORE);

    $pos_x = IMAGE_BORDERS;
    $pos_y = IMAGE_BORDERS;
    $max_line_height[] = 0;

    $sprites_count = count($config_array);
    $sprites_in_row = ceil($sprites_count / ROWS_COUNT);

    echo msg(', using ' . ROWS_COUNT . ' rows, ' . $sprites_in_row . ' sprites in a row', MSG_MORE);

    $i = 0;

    foreach ($config_array AS $sprite_config_key => $sprite_config_val) {
        $sprite_config = $config_array[$sprite_config_key];

        $placement_array[$sprite_config_key] = array(
            'x' => $pos_x,
            'y' => $pos_y,
            'width' => $sprite_config['x'],
            'height' => $sprite_config['y'],
            'path' => $sprite_config['path']
        );

        if ($i <= $sprites_in_row) {
            $pos_x = $pos_x + $sprite_config['x'];
            $row_height[] = $sprite_config['y'];
        } else {
            $pos_y = $pos_y + max($row_height);

            $canvas_width[] = $pos_x;
            $canvas_height[] = $pos_y;

            $pos_x = 0;
            unset($row_height);
            $i = 0;
        }

        $i++;
        echo msg('#' . $i . ': X:' . $pos_x . ' Y:' . $pos_y . nl, MSG_MORE);
    }

    echo msg('Done' . nl, MSG_MORE);
    return array(
        'canvas' => array(
            'width' => max($canvas_width),
            'height' => max($canvas_height) + max($row_height),
        ),
        'sprites' => $placement_array
    );
}

function calculate_positions($config_array) {
    if (ROWS_COUNT && COLS_COUNT) {
        return arrange_dimensions($config_array);
    } else {
        if (ROWS_COUNT) {
            return arrange_by_rows($config_array);
        } elseif (COLS_COUNT) {
            return arrange_by_cols($config_array);
        } else {
            return arrange_optimal($config_array);
        }
    }
}

function save_datauri($config_array) {
    echo msg('Saving DATA URIs...' . nl, MSG_MORE);
    foreach ($config_array AS $sprite_config_key => $sprite_config_val) {
        echo msg('Loading icon: ' . $sprite_config_val['path'] . ' ... ', MSG_MORE);

        if ($file = file_get_contents($sprite_config_val['path'])) {
            if ($encoded_file = base64_encode($file)) {
                echo msg("Done" . nl, MSG_MORE);
            } else {
                echo msg("ERROR: Failed encoding file to base64" . nl, MSG_MORE);
                die();
            }
        } else {
            echo msg("ERROR: File cannot be loaded" . nl, MSG_MORE);
            die();
        }

        $placement_array[$sprite_config_key] = array(
            'width' => $sprite_config_val['x'],
            'height' => $sprite_config_val['y'],
            'path' => $sprite_config_val['path'],
            'datauri' => $encoded_file,
        );
        unset($file, $encoded_file);
    }
    return array('sprites' => $placement_array);
}

function save_image($positions_array) {
    echo msg('Making sprite image...' . nl, MSG_MORE);

    if (TIMESTAMP) {
        $timestamp_font_height = 6; // font height in pixels
        if ($positions_array['canvas']['width'] < 100) {
            $positions_array['canvas']['width'] = 100;
        }
        $positions_array['canvas']['height'] += $timestamp_font_height;
    }

    if ($sprite_image = imagecreatetruecolor($positions_array['canvas']['width'], $positions_array['canvas']['height'])) {
        echo msg("Creating sprite canvas " . $positions_array['canvas']['width'] . "x" . $positions_array['canvas']['height'] . " pixels ... Done" . nl, MSG_MORE);
        echo msg("Setting transparent background... ", MSG_DEBUG);
        if (imagefill($sprite_image, 0, 0, imagecolorallocatealpha($sprite_image, 0, 0, 0, 127))) {
            echo msg("Done" . nl, MSG_DEBUG);
        } else {
            echo msg("ERROR: Failed to create transparent background" . nl, MSG_IMPORTANT);
            die();
        }

        foreach ($positions_array['sprites'] AS $sprite_config_key => $sprite_config_val) {
            $sprite_pos_x = $sprite_config_val['x'];
            $sprite_pos_y = $sprite_config_val['y'];
            $sprite_width = $sprite_config_val['width'];
            $sprite_height = $sprite_config_val['height'];
            $sprite_path = $sprite_config_val['path'];

            echo msg('Loading icon: ' . $sprite_path, MSG_MORE);
            if ($sprite_icon = loadimage($sprite_path)) {
                echo msg(', pos: ' . $sprite_pos_x . ',' . $sprite_pos_y . ' ', MSG_DEBUG);
                if (imagecopy($sprite_image, $sprite_icon, $sprite_pos_x, $sprite_pos_y, 0, 0, $sprite_width, $sprite_height)) {
                    echo msg("Done" . nl, MSG_MORE);
                } else {
                    echo msg("Failed" . nl, MSG_MORE);
                }
            } else {
                echo msg("Failed" . nl, MSG_MORE);
                die();
            }
        }
        if (IMAGE_PATH) {
            if (TIMESTAMP) {
                imagestring($sprite_image, 1, 0, $positions_array['canvas']['height'] - $timestamp_font_height - 1, date('Y-m-d H:i:s'), imagecolorallocate($sprite_image, 0, 0, 0));
            }
            echo msg('Saving new sprite image as ' . IMAGE_PATH . ' ... ', MSG_NORMAL);
            imagesavealpha($sprite_image, true);
            if (imagepng($sprite_image, EXTRA_OPTIMIZATION ? IMAGE_PATH . '-original.png' : IMAGE_PATH)) {
                echo msg('Done' . nl, MSG_NORMAL);
            } else {
                echo msg('Failed' . nl, MSG_NORMAL);
            }
            imagedestroy($sprite_image);
        }
    } else {
        echo msg("Cannot Initialize GD image library" . nl, MSG_IMPORTANT);
        die(0);
    }
}

function save_css($positions_array) {
    echo msg('Generating CSS rules', MSG_DEBUG);

    if (CSS_SHORT_CODE == true) {
        echo msg(', using shortcode', MSG_DEBUG);
        $css_rules[] = implode(',', array_keys($positions_array['sprites'])) . '{background:url(' . CSS_IMAGE_PATH . ') no-repeat 0 0}';
    }
    foreach ($positions_array['sprites'] AS $classname => $value) {
        $image_dimensions = ';width:' . $value['width'] . 'px;height:' . $value['height'] . 'px';

        if (DATAURI == true) {
            $css_rules[] = $classname . '{background:url(data:image/png;base64,' . $value['datauri'] . ') no-repeat 0 0' . $image_dimensions . '}';
        } else {
            $css_image_size = CSS_IMAGE_SIZE ? $image_dimensions : '';
            if (CSS_SHORT_CODE == true) {
                $css_rules[] = $classname . '{background-position:' . css_val($value['x']) . ' ' . css_val($value['y']) . $css_image_size . '}';
            } else {
                $css_rules[] = $classname . '{background:url(' . CSS_IMAGE_PATH . ') no-repeat ' . css_val($value['x']) . ' ' . css_val($value['y']) . $css_image_size . '}';
            }
        }
    }
    echo msg(' ... Done' . nl, MSG_DEBUG);

    echo msg('Saving new CSS as ' . CSS_PATH . ' ... ', MSG_NORMAL);
    if (file_put_contents(CSS_PATH, implode('', $css_rules))) {
        echo msg('Done' . nl, MSG_NORMAL);
        return true;
    } else {
        echo msg('Failed' . nl, MSG_NORMAL);
        return false;
    }
}

function save_html($positions_array) {
    echo msg('Generating HTML rules', MSG_DEBUG);
    $html_rules[] = '<link href="' . HTML_CSS_PATH . '" rel="stylesheet" type="text/css">';
    if (CSS_IMAGE_SIZE != true) {
        echo msg(', using inline style width and height information', MSG_DEBUG);
    }
    foreach ($positions_array['sprites'] AS $classname => $value) {
        $inline_size = (CSS_IMAGE_SIZE != true) ? $inline_size = ' style="width:' . $value['width'] . 'px; height:' . $value['height'] . 'px"' : '';
        $html_rules[] = '<div class="' . substr($classname, 1) . '"' . $inline_size . '></div>';
    }
    echo msg(' ... Done' . nl, MSG_DEBUG);

    echo msg('Saving new HTML as ' . HTML_PATH . ' ... ', MSG_NORMAL);
    if (file_put_contents(HTML_PATH, implode('', $html_rules))) {
        echo msg('Done' . nl, MSG_NORMAL);
        return true;
    } else {
        echo msg('Failed' . nl, MSG_NORMAL);
        return false;
    }
}

function css_val($val) {
    return $val > 0 ? '-' . $val . 'px' : '0';
}

function file_extension($file) {
    $ext = explode('.', $file);
    return strtolower(end($ext));
}

function file_name($filename) {
    $dot = strrpos($filename, '.');
    return substr($filename, 0, $dot);
}

function loadimage($file) {
    switch (file_extension($file)) {
        case "gif":
            $image = imagecreatefromgif($file);
            break;
        case "png":
            $image = imagecreatefrompng($file);
            break;
        case "jpg":
            $image = imagecreatefromjpeg($file);
            break;
    }
    return $image;
}

function help_present() {
    echo msg("Make me sprite! v0.3.2 by Cezary Srednicki <cezary@srednicki.info> 2011-".date('Y') . nl, MSG_IMPORTANT);
    echo msg("Create sprites easily with png and css optimization rules." . nl, MSG_NORMAL);
}

function help_info() {
    echo msg("USAGE: php -f makemesprite.php -- --config <file> --image <file>" . nl, MSG_IMPORTANT);
    echo msg("*** Don't forget to add -- after script filename ***" . nl . nl, MSG_IMPORTANT);
    echo msg("   --help                  shows help" . nl, MSG_IMPORTANT);
    echo msg("   --short                 use css shortcode" . nl, MSG_IMPORTANT);
    echo msg("   --wh                    put icon width and height definition in css file" . nl, MSG_IMPORTANT);
    /* echo msg( "   --max                   maximize compression, same as -s ".nl, MSG_IMPORTANT); */
    echo msg("   --padding [width]       icons padding width in pixels, default 0" . nl, MSG_IMPORTANT);
    echo msg("   --css [path]            output css file" . nl, MSG_IMPORTANT);
    echo msg("   --image [path]          output image file, required parameter" . nl, MSG_IMPORTANT);
    echo msg("   --html [path]           output html test file" . nl, MSG_IMPORTANT);
    echo msg("   --csspath [path]        image file path in css file" . nl, MSG_IMPORTANT);
    echo msg("   --htmlpath [path]       css file path in html file" . nl, MSG_IMPORTANT);
    echo msg("   --crush [pngcrush.exe path]  turn on extra optimization by PNG Crush" . nl, MSG_IMPORTANT);
    echo msg("   --optimal               arrange icons in optimal way" . nl, MSG_IMPORTANT);
    echo msg("   --datauri               instead of generating output image use DATA URIs in CSS" . nl, MSG_IMPORTANT);
    echo msg("   --timestamp             draw actual timestamp in output file" . nl, MSG_IMPORTANT);
    echo msg("   --rows [number]         arrange icons in rows, number set rows count" . nl, MSG_IMPORTANT);
    //echo msg( "   --cols [number]         arrange icons in columns, number set columns count".nl, MSG_IMPORTANT);
    echo msg("   --verbose [0-3]         verbose level" . nl, MSG_IMPORTANT);
    echo msg("       0                   shows only most important messages" . nl, MSG_IMPORTANT);
    echo msg("       1                   shows also normal messages (default)" . nl, MSG_IMPORTANT);
    echo msg("       2                   shows also debug messages" . nl, MSG_IMPORTANT);
    echo msg("       3                   shows all messages with extra debug information" . nl, MSG_IMPORTANT);
    echo msg("   --config <file>         sprite configuration file can work with 2 formats," . nl, MSG_IMPORTANT);
    echo msg("                           required parameter" . nl . nl, MSG_IMPORTANT);
    echo msg("       file1.png" . nl, MSG_IMPORTANT);
    echo msg("       file2.png" . nl, MSG_IMPORTANT);
    echo msg("       ;file3.png" . nl, MSG_IMPORTANT);
    echo msg("                           in this case classname is based on filename" . nl . nl, MSG_IMPORTANT);
    echo msg("       .classname1,file1.png" . nl, MSG_IMPORTANT);
    echo msg("       .classname2,file2.png" . nl, MSG_IMPORTANT);
    echo msg("       ;.classname3,file3.png" . nl, MSG_IMPORTANT);
    echo msg("                           in this case classname is defined by user" . nl, MSG_IMPORTANT);
    echo msg("                           also note that ; means commented line" . nl . nl, MSG_IMPORTANT);
}

function makemesprite() {
    $cmd_params = parseParameters();

    define('VERBOSE_LEVEL', isset($cmd_params['verbose']) && !empty($cmd_params['verbose']) && is_numeric($cmd_params['verbose']) ? $cmd_params['verbose'] : (isset($cmd_params['quiet']) ? MSG_OFF : MSG_NORMAL) );

    help_present();

    if (!function_exists("imagecreatetruecolor")) {
        die('FATAL ERROR: Cannot Initialize GD2 image library' . nl);
    }

    if (isset($cmd_params['help']) OR count($cmd_params) < 2) {
        help_info();
        die();
    }

    if (isset($cmd_params['config']) && !empty($cmd_params['config'])) {
        define('CONFIGURATION_FILE', $cmd_params['config']);
        echo msg('Using configuration file ' . $cmd_params['config'] . nl, MSG_MORE);
    } else {
        echo msg('ERROR: No configuration file given.' . nl, MSG_IMPORTANT);
        die(0);
    }

    if (isset($cmd_params['image']) && !empty($cmd_params['image'])) {
        define('IMAGE_PATH', $cmd_params['image']);
    } else {
        echo msg('ERROR: No output image path selected.' . nl, MSG_IMPORTANT);
        die(0);
    }

    define('IMAGE_BORDERS', isset($cmd_params['padding']) && !empty($cmd_params['padding']) && is_numeric($cmd_params['padding']) ? $cmd_params['padding'] : 0 );
    define('CSS_SHORT_CODE', isset($cmd_params['short']) && !empty($cmd_params['short']));
    define('CSS_IMAGE_SIZE', isset($cmd_params['wh']));
    define('CSS_PATH', $cmd_params['css'] ? $cmd_params['css'] : IMAGE_PATH );
    define('CSS_IMAGE_PATH', isset($cmd_params['csspath']) ? $cmd_params['csspath'] : IMAGE_PATH );
    define('HTML_CSS_PATH', isset($cmd_params['htmlpath']) ? $cmd_params['htmlpath'] : IMAGE_PATH );
    define('HTML_PATH', isset($cmd_params['html']) ? $cmd_params['html'] : false );
    define('OPTIMAL', isset($cmd_params['optimal']));
    define('DATAURI', isset($cmd_params['datauri']));
    define('TIMESTAMP', isset($cmd_params['timestamp']));
    define('ROWS_COUNT', isset($cmd_params['rows']) && !empty($cmd_params['rows']) && is_numeric($cmd_params['rows']) ? $cmd_params['rows'] : false );
    define('COLS_COUNT', isset($cmd_params['cols']) && !empty($cmd_params['cols']) && is_numeric($cmd_params['cols']) ? $cmd_params['cols'] : false );

    if (isset($cmd_params['crush']) && !empty($cmd_params['crush'])) {
        if (file_exists($cmd_params['crush'])) {
            define('EXTRA_OPTIMIZATION', true);
            define('PNGCRUSH_CMD', $cmd_params['crush'] . ' -q ' . IMAGE_PATH . '-original.png ' . IMAGE_PATH);
        } else {
            if ($cmd_params['crush'] == 1) {
                echo msg('ERROR: Please specify location of PNGCrush executable' . nl, MSG_IMPORTANT);
            } else {
                echo msg('ERROR: PNGCrush executable not found at ' . $cmd_params['crush'] . nl, MSG_IMPORTANT);
            }
            define('EXTRA_OPTIMIZATION', false);
            die(0);
        }
    } else {
        define('EXTRA_OPTIMIZATION', false);
    }
    if ($sprite_array = read_config($cfg_file)) {
        echo msg('Configuration file seems to be OK!' . nl, MSG_MORE);

        if (DATAURI == true) {
            $positions = save_datauri($sprite_array);
            save_css($positions);
        } else {
            $positions = calculate_positions($sprite_array);
            save_image($positions);
            save_css($positions);
        }

        if (HTML_PATH == true) {
            save_html($positions);
        }

        if (EXTRA_OPTIMIZATION == true) {
            echo msg('Applying extra optimization by PngCrush... ', MSG_NORMAL);
            system(PNGCRUSH_CMD, $cmd_return);
            echo msg('Done' . nl, MSG_NORMAL);
        }
    }
}

makemesprite();
?>