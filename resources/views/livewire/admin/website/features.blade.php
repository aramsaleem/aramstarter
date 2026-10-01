@php
    use App\Models\Feature;

    $tones = [
        'bg-primary-500/10 text-primary-600 ring-primary-500/20 dark:text-primary-300',
        'bg-cyan-500/10 text-cyan-600 ring-cyan-500/20 dark:text-cyan-300',
        'bg-emerald-500/10 text-emerald-600 ring-emerald-500/20 dark:text-emerald-300',
        'bg-amber-500/10 text-amber-600 ring-amber-500/25 dark:text-amber-300',
        'bg-sky-500/10 text-sky-600 ring-sky-500/20 dark:text-sky-300',
        'bg-rose-500/10 text-rose-600 ring-rose-500/20 dark:text-rose-300',
    ];
@endphp

<div>
    <x-ui.page-header :eyebrow="__('Website')" :title="__('Website features')" :description="__('The cards in the features section. The first card is shown large, the order here is the order on the website.')">
        <x-slot:actions>
            <x-ui.button icon="plus" wire:click="create">{{ __('Add feature') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-admin.website-nav />

    @if ($this->features->isEmpty())
        <x-ui.card :padding="false">
            <x-ui.empty-state icon="squares-plus" :title="__('No features yet')" :description="__('Add your first feature card. The section stays hidden on the website until there is one.')">
                <x-ui.button icon="plus" wire:click="create">{{ __('Add feature') }}</x-ui.button>
            </x-ui.empty-state>
        </x-ui.card>
    @else
        <div class="grid gap-4 md:grid-cols-2 2xl:grid-cols-3">
            @foreach ($this->features as $feature)
                <x-ui.card :padding="false" wire:key="feature-{{ $feature->id }}" @class(['flex flex-col transition', 'opacity-60' => ! $feature->is_active])>
                    <div class="flex flex-1 flex-col p-5">
                        <div class="flex items-start justify-between gap-3">
                            <span class="flex size-11 items-center justify-center rounded-xl ring-1 {{ $tones[$loop->index % count($tones)] }}">
                                <x-dynamic-component :component="'heroicon-o-'.(in_array($feature->icon, Feature::ICONS, true) ? $feature->icon : 'sparkles')" class="size-5" />
                            </span>

                            <div class="flex items-center gap-1.5">
                                @if ($loop->first)
                                    <x-ui.badge color="primary">{{ __('Large card') }}</x-ui.badge>
                                @endif
                                <span class="font-mono text-xs text-zinc-400" dir="ltr">#{{ $loop->iteration }}</span>
                            </div>
                        </div>

                        <h2 class="mt-4 font-semibold text-zinc-900 dark:text-white">{{ $feature->title }}</h2>
                        <p class="mt-1 line-clamp-2 text-sm text-zinc-500 dark:text-zinc-400">{{ $feature->description }}</p>

                        <div class="mt-auto flex flex-wrap items-center gap-2 pt-4">
                            @if ($feature->visual)
                                <x-ui.badge color="sky">
                                    <x-heroicon-m-sparkles class="size-3" />
                                    {{ Feature::visualLabel($feature->visual) }}
                                </x-ui.badge>
                            @endif
                            <x-admin.locale-status :model="$feature" field="title" />
                        </div>
                    </div>

                    <x-admin.content-controls
                        :id="$feature->id"
                        :active="$feature->is_active"
                        :first="$loop->first"
                        :last="$loop->last"
                        :name="$feature->title"
                        class="border-t border-zinc-200/80 px-4 py-2.5 dark:border-white/[0.07]"
                    />
                </x-ui.card>
            @endforeach
        </div>
    @endif

    <x-ui.modal wire:model="showModal" max-width="xl" icon="squares-plus" :title="$editingId ? __('Edit feature') : __('Add feature')">
        <form wire:submit="save" class="space-y-6">
            <fieldset>
                <legend class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Icon') }}</legend>
                <div class="mt-2 grid grid-cols-6 gap-1.5 sm:grid-cols-8">
                    @foreach (Feature::ICONS as $name)
                        <label title="{{ $name }}" class="flex aspect-square cursor-pointer items-center justify-center rounded-xl text-zinc-500 ring-1 ring-zinc-200 transition ring-inset hover:bg-zinc-50 hover:text-zinc-900 has-checked:bg-primary-500/10 has-checked:text-primary-600 has-checked:ring-2 has-checked:ring-primary-500 has-focus-visible:outline-2 has-focus-visible:outline-primary-500 dark:ring-white/10 dark:hover:bg-white/5 dark:hover:text-white dark:has-checked:text-primary-300">
                            <input type="radio" wire:model="icon" value="{{ $name }}" class="sr-only">
                            <x-dynamic-component :component="'heroicon-o-'.$name" class="size-5" />
                            <span class="sr-only">{{ $name }}</span>
                        </label>
                    @endforeach
                </div>
                <x-ui.error for="icon" class="mt-2" />
            </fieldset>

            <x-ui.translatable model="title" :label="__('Title')" :maxlength="80" required />
            <x-ui.translatable model="description" type="textarea" :label="__('Description')" :maxlength="240" rows="3" required />

            <x-ui.select wire:model="visual" :label="__('Animation')" :description="__('A small live illustration under the text.')">
                <option value="">{{ __('None') }}</option>
                @foreach (Feature::VISUALS as $visual)
                    <option value="{{ $visual }}">{{ Feature::visualLabel($visual) }}</option>
                @endforeach
            </x-ui.select>

            <x-ui.switch wire:model="is_active" :label="__('Visible on the website')" />

            <div class="flex justify-end gap-2 border-t border-zinc-200/80 pt-5 dark:border-white/[0.07]">
                <x-ui.button variant="secondary" x-on:click="show = false">{{ __('Cancel') }}</x-ui.button>
                <x-ui.button type="submit" loading="save">{{ __('Save') }}</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
