<?php

namespace Blocksy\DbVersioning;

class V2027 {
	public function migrate() {
		$values_cleaner = new \Blocksy\DbVersioning\DefaultValuesCleaner();
		$values_cleaner->clean_whole_customizer();
	}
}


