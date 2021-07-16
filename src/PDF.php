<?php

namespace FlawidDSouza\QuickAdminPanelLaravel;

use Spatie\Browsershot\Browsershot;

class PDF
{
    public static function fromBladeView($bladeView, $data, $pdfFileName, $paperSize, $params = [], $paramsData = [], $savePath = null)
    {
        $view = null;

        if(!in_array('bladeViewFromFilePath', $params)) {
            $view = view($bladeView, $data);
        } else {
            $view = view()->file($bladeView, $data);
        }

        $pdf = Browsershot::html($view->render());

        if(is_array($paperSize)) {
            $pdf = $pdf->paperSize(...$paperSize);
        } else {
            $pdf = $pdf->format($paperSize);
        }

        if(in_array('isLandscape', $params)) {
            $pdf = $pdf->landscape(true);
        }

        if(in_array('showBackground', $params)) {
            $pdf = $pdf->showBackground();
        }

        if(in_array('waitUntilNetworkIdle', $params)) {
            $pdf->waitUntilNetworkIdle();
        }

        if(in_array('showBrowserHeaderAndFooter', $params)) {
            $headerHtml = $paramsData['showBrowserHeaderAndFooter']['headerHtml'] ?? '';
            $footerHtml = $paramsData['showBrowserHeaderAndFooter']['footerHtml'] ?? '';

            $pdf->showBrowserHeaderAndFooter()
            ->headerHtml(trim($headerHtml) === '' ? '<div></div>' : $headerHtml)
            ->footerHtml(trim($footerHtml) === '' ? '<div></div>' : $footerHtml);
        }

        $pdf = $pdf->noSandbox();

        $pdf = $pdf->timeout(1200);

        if($savePath) {
            $pdf->savePdf($savePath);
        } else {
            // comment out the below 2 headers to see errors - currently there's no other way
            // to debug a pdf generation failure that's not related to incorrect html
            // generation - incorrect html generation can be debugged by just returning the
            // view, which will return html instead of pdf
            header('Content-type:application/pdf');
            header('Content-disposition: inline; filename="' . $pdfFileName . '"');
            echo $pdf->pdf();
            exit;
        }
    }
}
