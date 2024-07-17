<?php

namespace Hostinger\EasyOnboarding\Admin\Onboarding;

defined( 'ABSPATH' ) || exit;

class StepCategory {
    /**
     * @var string
     */
    private string $title = '';

    /**
     * @var string
     */
    private string $id = '';

    /**
     * @var array
     */
    private array $steps = array();

    public function __construct(string $id, string $title = '', array $steps = array()) {
        $this->title = $title;
        $this->id = $id;
        $this->steps = $steps;
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
     * @return array
     */
    public function get_steps(): array
    {
        return $this->steps;
    }

    /**
     * @param array $steps
     *
     * @return void
     */
    public function set_steps(array $steps): void
    {
        $this->steps = $steps;
    }

    /**
     * @param Step $step
     *
     * @return void
     */
    public function add_step(Step $step): void
    {
        $step = $this->update_step_status( $step );

        $this->steps[] = $step;
    }

    /**
     * @return array
     */
    public function to_array(): array
    {
        return array(
            'title' => $this->get_title(),
            'id' => $this->get_id(),
            'steps' => array_map(
                function ( $item ) {
                    return $item->to_array();
                },
                $this->get_steps()
            )
        );
    }

    /**
     * @param Step $step
     *
     * @return Step
     */
    public function update_step_status(Step $step): Step {
        $onboarding_steps = get_option( Onboarding::HOSTINGER_EASY_ONBOARDING_STEPS_OPTION_NAME, array() );

        if(empty($onboarding_steps[$this->get_id()][$step->get_id()])) {
            return $step;
        }

        $step->set_is_completed( (bool)$onboarding_steps[$this->get_id()][$step->get_id()] );

        return $step;
    }
}
