<?php

namespace HeimrichHannot\UtilsBundle\Util\Ui;

class PopupWizardLinkOptions
{
    /** @var string The route to generate the link */
    public string $route = 'contao_backend';
    /** @var bool If only the url should be returned instead of a complete link element */
    public bool $urlOnly = false;
    /** @var string The title of the link */
    public string $title = '';
    /** @var string Override the default css style properties */
    public string $style = 'display: inline-block;';
    /**  *@var array Additional Link attributes as key value pairs. Will override title and style option. href is not allowed and will be removed from list. */
    public array $attributes = [];
    /** @var string Link icon to show as link text. Overrides default icon. */
    public string $icon = 'alias.svg';
    /** @var string A linkTitle to show as link text. Will be displayed after the link icon. Default empty. */
    public string $linkText = '';
    /** @var int The width of the popup */
    public int $width = 991;
    /** @var string The title of the popup */
    public string $popupTitle = '';

    public function __construct()
    {
    }

    /**
     * Set the route to generate the link.
     */
    public function setRoute(string $route): PopupWizardLinkOptions
    {
        $this->route = $route;
        return $this;
    }

    /**
     * If only the url should be returned instead of a complete link element.
     */
    public function setUrlOnly(bool $urlOnly): PopupWizardLinkOptions
    {
        $this->urlOnly = $urlOnly;
        return $this;
    }

    /**
     * Set the title of the link.
     */
    public function setTitle(string $title): PopupWizardLinkOptions
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Override the default css style properties.
     */
    public function setStyle(string $style): PopupWizardLinkOptions
    {
        $this->style = $style;
        return $this;
    }

    /**
     * Set additional link attributes as key value pairs. Will override title and style option. href and onclick are not allowed and will be removed from list.
     */
    public function setAttributes(array $attributes): PopupWizardLinkOptions
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * Add an additional attribute to the link.
     */
    public function setIcon(string $icon): PopupWizardLinkOptions
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * Override the default link text. Will be displayed after the link icon.
     */
    public function setLinkText(string $linkText): PopupWizardLinkOptions
    {
        $this->linkText = $linkText;
        return $this;
    }

    /**
     * Set the title of the popup.
     */
    public function setPopupTitle(string $string): PopupWizardLinkOptions
    {
        $this->popupTitle = $string;
        return $this;
    }

    /**
     * Set the width of the popup.
     */
    public function setWidth(int $width): PopupWizardLinkOptions
    {
        $this->width = $width;
        return $this;
    }
}