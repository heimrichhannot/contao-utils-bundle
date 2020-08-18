# PdfCreator 

PdfCreator is a high level API for PDF writing with PHP. 

## Example

```php
use HeimrichHannot\UtilsBundle\PdfCreator\PdfCreatorFactory;
use HeimrichHannot\UtilsBundle\PdfCreator\Concrete\MpdfCreator;

$pdf = PdfCreatorFactory::createInstance(MpdfCreator::getType());
$pdf->setHtmlContent($this->compile())
    ->setFilename($this->getFileName())
    ->setFormat('A4')
    ->setOrientation($pdf::ORIENTATION_PORTRAIT)
    ->addFont(
        "/path_to_project/assets/fonts/my_great_font.tff", 
        "myGreatFont", 
        $pdf::FONT_STYLE_REGUALAR,
        "normal"
    )
    ->setMargins(15, 10, 15,10)
    ->setTemplateFilePath("/path_to_project/assets/pdf/mastertemplate.pdf")
    ->setOutputMode($pdf::OUTPUT_MODE_DOWNLOAD)
    ->render()
;
```