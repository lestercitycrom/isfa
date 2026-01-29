<?php

declare(strict_types=1);

namespace App\Livewire\Admin\ImportExport;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
final class Index extends Component
{
	public function render(): View
	{
		return view('livewire.admin.import-export.index');
	}
}
