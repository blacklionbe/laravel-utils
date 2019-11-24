<?php

use Illuminate\Support\Facades\Date;

if (! function_exists('carbon')) {
    function carbon($time = null, $tz = null)
    {
        return Date::parse($time, $tz);
    }
}

if (! function_exists('extension')) {
    function extension($filename)
    {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }
}

if (! function_exists('ie')) {
    function ie()
    {
        $agent = htmlentities(request()->server('HTTP_USER_AGENT'), ENT_QUOTES, 'UTF-8');

        return preg_match('~MSIE|Internet Explorer~i', $agent) || (strpos($agent, 'Trident/7.0') !== false && strpos($agent, 'rv:11.0') !== false);
    }
}
