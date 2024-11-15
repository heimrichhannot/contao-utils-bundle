<?php

namespace HeimrichHannot\UtilsBundle\Util;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Image;
use Contao\StringUtil;
use HeimrichHannot\UtilsBundle\Util\Html\HtmlUtil;
use HeimrichHannot\UtilsBundle\Util\Routing\RoutingUtil;
use HeimrichHannot\UtilsBundle\Util\Ui\PopupWizardLinkOptions;

class BackendUiUtil
{
    private RoutingUtil $routingUtil;
    private ContaoFramework $framework;
    private HtmlUtil $htmlUtil;

    public function __construct(
        RoutingUtil     $routingUtil,
        ContaoFramework $framework,
        HtmlUtil        $htmlUtil
    )
    {
        $this->routingUtil = $routingUtil;
        $this->framework = $framework;
        $this->htmlUtil = $htmlUtil;
    }

    /**
     * Create a popup wizard link or url.
     */
    public function popupWizardLink(array $parameter, PopupWizardLinkOptions $options): string
    {
        $parameter['popup'] = 1;
        $parameter['nb'] = 1;

        $url = $this->routingUtil->generateBackendRoute($parameter, true, true, $options->route);
        if ($options->urlOnly) {
            return $url;
        }

        $attributes = $options->attributes;
        if (empty($options->title)) {
            $title = $GLOBALS['TL_LANG']['tl_content']['edit'][0];
        } else {
            $title = StringUtil::specialchars($options->title);
        }

        if (!isset($attributes['title'])) {
            $attributes['title'] = $title;
        }

        if (!isset($attributes['style'])) {
            $attributes['style'] = $options->style;
        }

        // onclick
        if (empty($attributes['onclick'])) {
            if (empty($options->popupTitle)) {
                $popupTitle = $title;
            } else {
                $popupTitle = $options->popupTitle;
            }

            $attributes['onclick'] = sprintf(
                'Backend.openModalIframe({\'width\':%s,\'title\':\'%s'.'\',\'url\':this.href});return false',
                $options->width,
                $popupTitle
            );
        }

        // link text and icon
        $linkText = '';

        if (!empty($options->icon)) {
            /** @var Image $image */
            $image = $this->framework->getAdapter(Image::class);
            $linkText .= $image->getHtml($options->icon, $title, 'style="vertical-align:top"');
        }

        if (!empty($options->linkText)) {
            $linkText = trim($linkText . ' '.$options->linkText);
        }

        // remove href from attributes if set
        unset($attributes['href']);

        return sprintf(
            '<a href="%s" %s>%s</a>',
            $url,
            $this->htmlUtil->generateAttributeString($attributes),
            $linkText
        );
    }
}