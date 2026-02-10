<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

trait InteractsWithNotifications
{
	protected function notifySuccess(string $message, ?int $timeout = null): void
	{
		$this->dispatchNotify('success', $message, $timeout);
	}

	protected function notifyError(string $message, ?int $timeout = null): void
	{
		$this->dispatchNotify('error', $message, $timeout);
	}

	protected function notifyInfo(string $message, ?int $timeout = null): void
	{
		$this->dispatchNotify('info', $message, $timeout);
	}

	protected function flashSuccessToast(string $message, ?int $timeout = null): void
	{
		$this->flashToast('success', $message, $timeout);
	}

	protected function flashErrorToast(string $message, ?int $timeout = null): void
	{
		$this->flashToast('error', $message, $timeout);
	}

	private function dispatchNotify(string $type, string $message, ?int $timeout): void
	{
		$this->dispatch('notify', type: $type, message: $message, timeout: $timeout ?? 3500);
	}

	private function flashToast(string $type, string $message, ?int $timeout): void
	{
		session()->flash('toast', [
			'type' => $type,
			'message' => $message,
			'timeout' => $timeout ?? 3500,
		]);
	}
}

