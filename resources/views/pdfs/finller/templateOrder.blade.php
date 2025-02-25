<!DOCTYPE html>
<html lang="en">

<head>
    <title></title>
    {{-- <title>{{ $order->reference }}</title> --}}
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @include('pdfs.finller.default.style')

</head>

<body>

    @include('pdfs.finller.order')

    <script type="text/php">
    if (isset($pdf)) {
        $pageCount = $pdf->get_page_count();
        
        if ($pageCount > 1) {
            $text = "Page {PAGE_NUM} / {PAGE_COUNT}";
            $size = 6;
            $defaultFont = $fontMetrics->getOptions()->getDefaultFont();
            $font = $fontMetrics->getFont($defaultFont);
            $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
            $x = ($pdf->get_width() - $width);
            $y = $pdf->get_height() - 35;
            $pdf->page_text($x, $y, $text, $font, $size);
        }
    }
    </script>

</body>

</html>
