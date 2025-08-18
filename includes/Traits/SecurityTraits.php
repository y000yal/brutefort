<?php

namespace BruteFort\Traits;
trait SecurityTraits {
	public function brutefort_get_ip() {
		$ip = '';
		if (isset($_SERVER['REMOTE_ADDR'])) {
			$ip = wp_unslash($_SERVER['REMOTE_ADDR']);
		} else {
			$ip = 'unknown';
		}
		return sanitize_text_field($ip);
	}
}