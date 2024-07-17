<?php

namespace Blocksy;

class ArchiveLogic {
	use WordPressActionsManager;

	private $doing_card_output = false;

	private $actions = [
		['action' => 'blocksy:loop:before'],
		['action' => 'blocksy:loop:after'],
	];

	public function __construct() {
		$this->attach_hooks();
	}

	public function blocksy_loop_before() {
		$this->doing_card_output = true;
	}

	public function blocksy_loop_after() {
		$this->doing_card_output = false;
	}

	public function is_doing_card_output() {
		return $this->doing_card_output;
	}
}
