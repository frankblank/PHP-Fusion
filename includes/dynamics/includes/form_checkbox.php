<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: form_checkbox.php
| Author: Frederick MC Chan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

use PHPFusion\Steam;

/**
 * Checkbox Input
 *
 * @param        $input_name
 * @param string $label
 * @param string $input_value
 * @param array  $options
 *
 * @return string
 */
function form_checkbox($input_name, $label = '', $input_value = '0', array $options = []) {

    $locale = fusion_get_locale('', LOCALE.LOCALESET.'global.php');

    $default_options = array(
        "input_id"        => $input_name,
        "inline"          => FALSE,
        "inline_options"  => FALSE,
        "required"        => FALSE,
        "deactivate"      => FALSE,
        "class"           => "",
        "button_class"    => "btn-default", // default, success, danger, warning, info
        "type"            => "checkbox",
        "default_checked" => TRUE,
        "toggle"          => FALSE,
        "toggle_text"     => [$locale["no"], $locale["yes"]],
        "options"         => [],
        "options_value"   => [],
        "delimiter"       => ",",
        "safemode"        => FALSE,
        "keyflip"         => FALSE,
        "error_text"      => $locale["error_input_checkbox"],
        "value"           => 1,
        "tip"             => "",
        "ext_tip"         => "",
        "inner_width"     => "",
        "reverse_label"   => FALSE,
        "deactivate_key"  => NULL,
        "onclick"         => "",
        "stacked"         => "",
    );

    $options += $default_options;

    $title = $label ?: ucfirst(strtolower(str_replace('_', ' ', $input_name)));

    $options['input_id'] = trim(str_replace("[", "-", $options['input_id']), "]");

    Defender::add_field_session([
        'input_name' => clean_input_name($input_name),
        'title'      => clean_input_name($title),
        'id'         => $options['input_id'],
        'type'       => $options['type'],
        'required'   => $options['required'],
        'safemode'   => $options['safemode'],
        'error_text' => $options['error_text'],
        'delimiter'  => $options['delimiter'],
    ]);

    if (Defender::inputHasError($input_name)) {
        // $error_class = "has-error ";
        if (!empty($options['error_text'])) {
            $new_error_text = Defender::getErrorText($input_name);
            if (!empty($new_error_text)) {
                $options['error_text'] = $new_error_text;
            }
            add_notice("danger", "<strong>$title</strong> - ".$options['error_text']);
        }
    }

    $options["option_labels"] = array(
        "on"  => $options['toggle_text'][1],
        "off" => $options['toggle_text'][0]
    );
    if ($options['keyflip']) {
        $options["options_labels"] = array(
            "on"  => $options['toggle_text'][0],
            "off" => $options['toggle_text'][1]
        );
    }

    if (!empty($options['options']) && is_array($options['options'])) {
        $options['toggle'] = FALSE; // force toggle to be false if options existed
        if (!empty($input_value)) {
            if (is_array($input_value)) {
                $option_value = array_flip($input_value);
                //$option_value = array_flip(explode($options['delimiter'], (string)$input_value)); // require key to value
            } else {
                $option_value = array_flip(explode($options['delimiter'], (string)$input_value)); // require key to value
            }
        }

        // for checkbox only
        // if there are options, and i want the options to be having input value.
        // options_value

    }

    return Steam::getInstance()->load('Form')->checkbox($input_name, $label, $input_value, $options);
}
