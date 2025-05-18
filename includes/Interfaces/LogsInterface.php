<?php

namespace BruteFort\Interfaces;

use \BruteFort\Interfaces\BaseInterface;

interface LogsInterface extends BaseInterface {
	public function get_log_by_ip( $ip ): array;
}