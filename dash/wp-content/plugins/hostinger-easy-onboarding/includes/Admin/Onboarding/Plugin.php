<?php

namespace Hostinger\EasyOnboarding\Admin\Onboarding;

defined( 'ABSPATH' ) || exit;

class Plugin {
    /**
     * @var string
     */
    private string $icon_url = '';

    /**
     * @var string
     */
    private string $name = '';
    /**
     * @var string
     */
    private string $slug = '';

    /**
     * @var string
     */
    private string $description = '';

    /**
     * @var array|mixed
     */
    private array $locale_supported = array();

    /**
     * @var bool
     */
    private bool $global = false;

    /**
     * @var string
     */
    private string $type = '';

    /**
     * @var bool
     */
    private bool $is_active = false;

    /**
     * @var bool
     */
    private bool $is_recommended = false;

    /**
     * @var bool
     */
    private bool $is_installed = false;

    /**
     * @param $icon_url
     * @param $name
     * @param $slug
     * @param $description
     * @param $type
     * @param $locale_supported
     * @param $global
     */
    public function __construct($icon_url, $name, $slug, $description, $type, $locale_supported, $global) {
        $this->icon_url = $icon_url;
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
        $this->type = $type;
        $this->locale_supported = $locale_supported;
        $this->global = $global;
    }

    /**
     * @return string
     */
    public function get_icon_url(): string
    {
        return $this->icon_url;
    }

    /**
     * @param string $icon_url
     */
    public function set_icon_url(string $icon_url): void
    {
        $this->icon_url = $icon_url;
    }

    /**
     * @return string
     */
    public function get_name(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function set_name(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function get_slug(): string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function set_slug(string $slug): void
    {
        $this->slug = $slug;
    }

    /**
     * @return string
     */
    public function get_description(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function set_description(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return array
     */
    public function get_locale_supported(): array
    {
        return $this->locale_supported;
    }

    /**
     * @param array $locale_supported
     */
    public function set_locale_supported(array $locale_supported): void
    {
        $this->locale_supported = $locale_supported;
    }

    /**
     * @return bool
     */
    public function get_global(): bool
    {
        return $this->global;
    }

    /**
     * @param bool $global
     */
    public function set_global(bool $global): void
    {
        $this->global = $global;
    }

    /**
     * @return string
     */
    public function get_type(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function set_type(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return bool
     */
    public function get_is_active(): bool
    {
        return $this->is_active;
    }

    /**
     * @param bool $is_active
     */
    public function set_is_active(bool $is_active): void
    {
        $this->is_active = $is_active;
    }

    /**
     * @return bool
     */
    public function get_is_recommended(): bool
    {
        return $this->is_recommended;
    }

    /**
     * @param bool $is_recommended
     */
    public function set_is_recommended(bool $is_recommended): void
    {
        $this->is_recommended = $is_recommended;
    }

    /**
     * @return bool
     */
    public function get_is_installed(): bool
    {
        return $this->is_installed;
    }

    /**
     * @param bool $is_installed
     */
    public function set_is_installed(bool $is_installed): void
    {
        $this->is_installed = $is_installed;
    }

    /**
     * @return array
     */
    public function to_array(): array {
        return array(
            'icon_url' => $this->get_icon_url(),
            'name' => $this->get_name(),
            'slug' => $this->get_slug(),
            'description' => $this->get_description(),
            'locale_supported' => $this->get_locale_supported(),
            'global' => $this->get_global(),
            'type' => $this->get_type(),
            'is_recommended' => $this->get_is_recommended(),
            'is_active' => $this->get_is_active(),
            'is_installed' => $this->get_is_installed()
        );
    }
}