<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Suppliers;

use App\Models\Supplier;
use App\Models\Tag;
use App\Support\CompanyContext;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

#[Layout('layouts.admin')]
final class Show extends Component
{
    public Supplier $supplier;

    public string $tab = 'details';

    public ?string $comment = null;

    public string $tagInput = '';

    public bool $showTagDropdown = false;

    public function mount(Supplier $supplier): void
    {
        $companyId = CompanyContext::companyId();
        $isAdmin = CompanyContext::isAdmin();

        if (! $isAdmin && $companyId !== null && (int) $supplier->company_id !== $companyId) {
            abort(403);
        }

        $this->supplier = $supplier->load([
            'products' => function ($q) use ($companyId): void {
                if ($companyId !== null) {
                    $q->where('products.company_id', $companyId);
                }

                $q->with('category');
            },
            'tags',
        ]);

        $this->comment = $this->supplier->comment;
    }

    public function render(): View
    {
        return view('livewire.admin.suppliers.show', [
            'activities' => $this->loadActivities(),
            'tagSuggestions' => $this->tagSuggestions(),
        ]);
    }

    public function updatedTagInput(): void
    {
        $this->showTagDropdown = true;
    }

    public function addTagFromInput(): void
    {
        $name = trim(preg_replace('/\s+/u', ' ', $this->tagInput) ?? '');
        if ($name === '') {
            return;
        }

        $slug = Str::slug($name);
        if ($slug === '') {
            $slug = Str::lower(str_replace(' ', '-', $name));
        }

        if ($slug === '') {
            return;
        }

        $tag = Tag::query()->firstOrCreate(
            [
                'company_id' => $this->tagCompanyId(),
                'slug' => $slug,
            ],
            [
                'name' => $name,
            ]
        );

        $this->supplier->tags()->syncWithoutDetaching([$tag->id]);
        $this->supplier->load('tags');
        $this->tagInput = '';
        $this->showTagDropdown = false;
    }

    public function selectTag(int $tagId): void
    {
        $exists = Tag::query()
            ->whereKey($tagId)
            ->where(function ($query): void {
                $companyId = $this->tagCompanyId();

                if ($companyId === null) {
                    $query->whereNull('company_id');

                    return;
                }

                $query->where('company_id', $companyId);
            })
            ->exists();

        if (! $exists) {
            return;
        }

        $this->supplier->tags()->syncWithoutDetaching([$tagId]);
        $this->supplier->load('tags');
        $this->tagInput = '';
        $this->showTagDropdown = false;
    }

    public function removeTag(int $tagId): void
    {
        $this->supplier->tags()->detach($tagId);
        $this->supplier->load('tags');
    }

    public function setTab(string $tab): void
    {
        $allowed = ['details', 'history', 'comments'];

        $this->tab = in_array($tab, $allowed, true) ? $tab : 'details';
    }

    public function saveComment(): void
    {
        if (! Schema::hasColumn($this->supplier->getTable(), 'comment')) {
            session()->flash('status', __('common.comment_column_missing'));

            return;
        }

        $this->supplier->update([
            'comment' => $this->comment,
        ]);

        $this->dispatch('comment-saved');
    }

    /**
     * @return Collection<int, Activity>
     */
    private function loadActivities(): Collection
    {
        return Activity::query()
            ->where('subject_type', $this->supplier->getMorphClass())
            ->where('subject_id', $this->supplier->getKey())
            ->latest()
            ->limit(50)
            ->get();
    }

    /**
     * @return Collection<int, Tag>
     */
    private function tagSuggestions(): Collection
    {
        $selectedIds = $this->supplier->tags->pluck('id')->map(static fn (mixed $id): int => (int) $id)->all();

        $query = Tag::query()
            ->where(function ($query): void {
                $companyId = $this->tagCompanyId();

                if ($companyId === null) {
                    $query->whereNull('company_id');

                    return;
                }

                $query->where('company_id', $companyId);
            })
            ->whereNotIn('id', $selectedIds)
            ->orderBy('name');

        $search = trim($this->tagInput);
        if ($search !== '') {
            $query->where('name', 'like', '%'.$search.'%');
        }

        return $query->limit(8)->get();
    }

    private function tagCompanyId(): ?int
    {
        return $this->supplier->company_id !== null ? (int) $this->supplier->company_id : null;
    }
}
