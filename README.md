# Contao Utils Bundle

[![](https://img.shields.io/packagist/v/heimrichhannot/contao-utils-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-utils-bundle)
[![](https://img.shields.io/packagist/dt/heimrichhannot/contao-utils-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-utils-bundle)
[![Build Status](https://travis-ci.org/heimrichhannot/contao-utils-bundle.svg?branch=master)](https://travis-ci.org/heimrichhannot/contao-utils-bundle)
[![Coverage Status](https://coveralls.io/repos/github/heimrichhannot/contao-utils-bundle/badge.svg?branch=master)](https://coveralls.io/github/heimrichhannot/contao-utils-bundle?branch=master)

This bundle offers various utility functionality for the Contao CMS.


## Install 

```
composer require heimrichhannot/contao-utils-bundle
```


## Usage

This Bundle is a collection of utils to solve recurring tasks. See the [Documentation](https://heimrichhannot.github.io/contao-utils-bundle/) to see all functions. 
We recommend to call the utils as service. You can either inject them (the Symfony recommend way) or call them from the service container (all util services are public).

Available [Service](src/Resources/config/services.yml) (as at version 2.1):

```php
huh.utils.array
huh.utils.cache.remote_image_cache
huh.utils.code
huh.utils.encryption
huh.utils.container
huh.utils.database
huh.utils.date
huh.utils.dca
huh.utils.file
huh.utils.form
huh.utils.image
huh.utils.location
huh.utils.member
huh.utils.model
huh.utils.request.curl
huh.utils.string
huh.utils.url
huh.utils.choice.field
huh.utils.choice.data_container
huh.utils.choice.message
huh.utils.choice.model_instance
huh.utils.choice.twig_template
huh.utils.routing
huh.utils.class
huh.utils.member
huh.utils.template
huh.utils.user
huh.utils.pdf_writer
```

## huh.utils.pdf

Example to create a custom pdf.
```
$pdf = System::getContainer()->get('huh.utils.pdf_writer')
            ->mergeConfig(['margin_left' => 15, 'margin_right' => 15, 'margin_top' => 15, 'margin_bottom' => 15])
            ->setHtml('<style>h1{color: red;}</style><h1>PDF-Example</h1>')
            ->addFontDirectories(StringUtil::trimsplit(',', 'files/pdf-fonts/fonts,web/build/fonts'))
            ->setFileName('test.pdf');

        if (null !== ($masterTemplatePath = System::getContainer()->get('huh.utils.file')->getPathFromUuid($this->readerConfigElement->syndicationPdfMasterTemplate))) {
            $pdf->setTemplate($masterTemplatePath);
        }

        $pdf->generate($this->download);
```

##### Use custom fonts  

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