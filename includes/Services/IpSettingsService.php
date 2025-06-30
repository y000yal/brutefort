<?php

namespace BruteFort\Services;

class IpSettingsService {


	public function get_all_ips($type = 'all') {
		$types = [
			'whitelisted' => 'bf_whitelisted_ips',
			'blacklisted' => 'bf_blacklisted_ips'
		];

		if ($type === 'all') {
			$ips = [];
			foreach ($types as $option) {
				$ips = array_merge($ips, json_decode(get_option($option, '[]'), true));
			}
			return $ips;
		}

		if (isset($types[$type])) {
			return json_decode(get_option($types[$type], '[]'), true);
		}

		return [];
	}
}