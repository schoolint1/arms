<?php

namespace App\API;

trait InkHelpers {
    private function str_helper($replace, $str) {
        return str_replace(['${teacher}', '${ps_help}'], $replace, $str);
    }
}