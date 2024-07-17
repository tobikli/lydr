<?php

namespace Blocksy\DbVersioning;

class V2026 {
	public function migrate() {
		$values_cleaner = new \Blocksy\DbVersioning\DefaultValuesCleaner();

		$values_cleaner->clean_header();
		$values_cleaner->clean_footer();
	}
}

