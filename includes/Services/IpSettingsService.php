<?php

namespace BruteFort\Services;

class IpSettingsService extends BaseService
{

	public function validate_and_sanitize_ip_settings($params)
	{
		$normal_validation = $this->validate_and_sanitize_settings($params);
		if (!empty($normal_validation['errors'])) {
			return $normal_validation;
		}
		if ($this->check_ip_exists($normal_validation['sanitized']['bf_ip_address'])) {
			$normal_validation['errors'] = array(
				'field' => 'bf_ip_address',
				'message' => 'Entry already exists.'
			);
		}
		return $normal_validation;
	}
	public function get_all_ips($type = 'all')
	{
		$types = [
			'whitelist' => 'bf_whitelisted_ips',
			'blacklist' => 'bf_blacklisted_ips'
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

	public function check_ip_exists($ip, $type = null)
	{
		$all_ips = $this->get_all_ips();
		foreach ($all_ips as $entry) {
			if (is_array($entry) && isset($entry['bf_ip_address']) && $ip === $entry['bf_ip_address']) {
				if(null !== $type) {
					if($type !== $entry['bf_list_type']) {
						return false;
					}
				}
				return true;
			}
		}
		return false;
	}
}