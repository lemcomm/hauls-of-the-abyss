<?php

namespace App\Entity;

class GuideKeeper {
	private string $route;
	private string $reason;

	public function __construct(string $route, string $reason) {
		$this->route = $route;
		$this->reason = $reason;
	}

	public function getRoute(): string {
		return $this->route;
	}

	public function getReason(): string {
		return $this->reason;
	}
}
