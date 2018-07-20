# PDF Writer `huh.utils.pdf.writer`

Example to create a custom pdf.
```
$pdf = System::getContainer()->get('huh.utils.pdf.writer')
            ->mergeConfig(['margin_left' => 15, 'margin_right' => 15, 'margin_top' => 15, 'margin_bottom' => 15])
            ->setHtml('<style>h1{color: red;}</style><h1>PDF-Example</h1>')
            ->addFontDirectories(StringUtil::trimsplit(',', 'files/pdf-fonts/fonts,web/build/fonts'))
            ->setFileName('test.pdf');

        if (null !== ($masterTemplatePath = System::getContainer()->get('huh.utils.file')->getPathFromUuid($this->readerConfigElement->syndicationPdfMasterTemplate))) {
            $pdf->setTemplate($masterTemplatePath);
        }

        $pdf->generate($this->download);
```

## Use custom fonts  

You can provide multiple paths to a directory containing additional fonts.
The directory **must contain** a `mpdf-config.php` file, that must return an array with the additional mpdf font-configuration.

**Example:**

You declare for instance the direcory `files/pdf-fonts/` that contains the `.ttf` or `.otf` or `.ttc` font files and the `mpdf-config.php`, than the following configuration should be made. 

```
<?php

$defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
$fontData          = $defaultFontConfig['fontdata'];

return [
    'fontdata'     => $fontData +[
        'roboto'      => [
            'R' => 'Roboto-Regular.ttf'
        ],
        'fontawesome' => [
            'R' => 'fontawesome-webfont.ttf'
        ]
    ],
    'default_font' => 'roboto'
];
``` 
*Example: mpdf-config.php*

More Information: https://mpdf.github.io/fonts-languages/fonts-in-mpdf-7-x.html