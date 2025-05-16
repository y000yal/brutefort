<?php

namespace BruteFort\Traits;
trait SecurityTraits {
	public function brutefort_get_ip() {
		return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
	}
}