<?php

namespace BlackLion\LaravelUtils;

use Illuminate\Support\Facades\Storage;

class PdfService
{
    public function viewToPdf($view, $data = [])
    {
        $snappy = app('snappy.pdf');
        $snappy->setOption('encoding', 'UTF-8');
        $snappy->setOption('page-size', 'A4');

        $html = view($view, $data)->render();

        return $snappy->getOutputFromHtml($html);
    }

    public function viewToPdfUrl($view, $data = [])
    {
        $filename = 'downloads/'.str_random(40);
        Storage::put($filename, $this->viewToPdf($view, $data));

        return Storage::url($filename);
    }
}
