<?php

namespace Hostinger\EasyOnboarding\Admin\Onboarding;

defined( 'ABSPATH' ) || exit;

class Step {
    /**
     * @var string
     */
    private string $id = '';

    /**
     * @var bool
     */
    private bool $is_completed = false;

    /**
     * @var string
     */
    private string $title = '';

    /**
     * @var string
     */
    private string $description = '';

    /**
     * @var array
     */
    private array $bullet_points = array();

    /**
     * @var string
     */
    private string $component_name = '';

    /**
     * @var string
     */
    private string $image_url = '';

    /**
     * @var string
     */
    private string $button_name = '';

    /**
     * @var string
     */
    private string $skip_button_name = '';

    /**
     * @var string
     */
    private string $url = '';

    /**
     * @param string $id
     * @param string $title
     * @param string $description
     * @param array  $bullet_points
     * @param string $component_name
     * @param string $image_url
     * @param string $button_name
     * @param string $url
     */
    public function __construct(string $id, string $title = '', string $description = '', array $bullet_points = array(), string $component_name = '', string $image_url = '', string $button_name = '', string $url = '') {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->bullet_points = $bullet_points;
        $this->component_name = $component_name;
        $this->image_url = $image_url;
        $this->button_name = empty($button_name) ? __( 'Take me there', 'hostinger-easy-onboarding' ) : $button_name;
        $this->skip_button_name = __( 'Skip', 'hostinger-easy-onboarding' );
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function get_id(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return void
     */
    public function set_id(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return bool
     */
    public function get_is_completed(): bool
    {
        return $this->is_completed;
    }

    /**
     * @param bool $is_completed
     *
     * @return void
     */
    public function set_is_completed(bool $is_completed): void
    {
        $this->is_completed = $is_completed;
    }

    /**
     * @return string
     */
    public function get_title(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return void
     */
    public function set_title(string $title): void
    {
        $this->title = $title;
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
     *
     * @return void
     */
    public function set_description(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return array
     */
    public function get_bullet_points(): array
    {
        return $this->bullet_points;
    }

    /**
     * @param array $bullet_points
     *
     * @return void
     */
    public function set_bullet_points(array $bullet_points): void
    {
        $this->bullet_points = $bullet_points;
    }

    /**
     * @return string
     */
    public function get_component_name(): string
    {
        return $this->component_name;
    }

    /**
     * @param string $component_name
     *
     * @return void
     */
    public function set_component_name(string $component_name): void
    {
        $this->component_name = $component_name;
    }

    /**
     * @return string
     */
    public function get_image_url(): string
    {
        return $this->image_url;
    }

    /**
     * @param string $image_url
     *
     * @return void
     */
    public function set_image_url(string $image_url): void
    {
        $this->image_url = $image_url;
    }

    /**
     * @return string
     */
    public function get_button_name(): string
    {
        return $this->button_name;
    }

    /**
     * @param string $button_name
     *
     * @return void
     */
    public function set_button_name(string $button_name): void
    {
        $this->button_name = $button_name;
    }

    /**
     * @return string
     */
    public function get_url(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return void
     */
    public function set_url(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function get_skip_button_name(): string
    {
        return $this->skip_button_name;
    }

    /**
     * @param string $skip_button_name
     */
    public function set_skip_button_name(string $skip_button_name): void
    {
        $this->skip_button_name = $skip_button_name;
    }

    /**
     * @return array
     */
    public function to_array(): array
    {
        return array(
            'id'     => $this->get_id(),
            'is_completed'     => $this->get_is_completed(),
            'title'     => $this->get_title(),
            'description'     => $this->get_description(),
            'bullet_points'     => $this->get_bullet_points(),
            'component_name'     => $this->get_component_name(),
            'image_url'     => $this->get_image_url(),
            'button_name'     => $this->get_button_name(),
            'skip_button_name' => $this->get_skip_button_name(),
            'url'     => $this->get_url()
        );
    }
}
