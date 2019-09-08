<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

if (! function_exists('carbon')) {
    function carbon($time = null, $tz = null)
    {
        return Carbon::parse($time, $tz);
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

if (! function_exists('view_to_pdf')) {
    function view_to_pdf($view = null, $data = [])
    {
        $snappy = app('snappy.pdf');
        $snappy->setOption('encoding', 'UTF-8');
        $snappy->setOption('page-size', 'A4');

        $html = view($view, $data)->render();

        return $snappy->getOutputFromHtml($html);
    }
}

if (! function_exists('view_to_pdf_url')) {
    function view_to_pdf_url($view = null, $data = [])
    {
        $filename = 'downloads/'.str_random(40);
        Storage::put($filename, view_to_pdf($view, $data));

        return Storage::url($filename);
    }
}
