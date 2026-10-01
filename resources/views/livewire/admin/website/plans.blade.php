@php
    use App\Models\Plan;
    use App\Support\Localization;

    $ctaPlaceholders = [];

    foreach (Localization::codes() as $locale) {
        $ctaPlaceholders[$locale] = __('Get started', [], $locale);
    }
@endphp

<div>
    <x-ui.page-header :eyebrow="__('Website')" :title="__('Website pricing')" :description="__('Plans in the pricing section. Leave the yearly price empty to offer monthly billing only.')">
        <x-slot:actions>
            <x-ui.button icon="plus" wire:click="create">{{ __('Add plan') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-admin.website-nav />

    @if ($this->plans->isEmpty())
        <x-ui.card :padding="false">
            <x-ui.empty-state icon="banknotes" :title="__('No plans yet')" :description="__('Add a plan. The pricing section stays hidden on the website until there is one.')">
                <x-ui.button icon="plus" wire:click="create">{{ __('Add plan') }}</x-ui.button>
            </x-ui.empty-state>
        </x-ui.card>
    @else
        <div class="grid gap-4 lg:grid-cols-2 2xl:grid-cols-3">
            @foreach ($this->plans as $plan)
                <x-ui.card :padding="false" wire:key="plan-{{ $plan->id }}" @class([
                    'flex flex-col transition',
                    'ring-2 ring-primary-500/60 dark:ring-primary-400/50' => $plan->is_featured,
                    'opacity-60' => ! $plan->is_active,
                ])>
                    <div class="flex flex-1 flex-col p-6">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h2 class="truncate font-semibold text-zinc-900 dark:text-white">{{ $plan->name }}</h2>
                                <p class="mt-1 line-clamp-2 text-sm text-zinc-500 dark:text-zinc-400">{{ $plan->description }}</p>
                            </div>

                            @if ($plan->is_featured)
                                <x-ui.badge color="primary" dot>{{ __('Most popular') }}</x-ui.badge>
                            @endif
                        </div>

                        <div class="mt-5 flex items-baseline gap-1.5" dir="ltr">
                            <span class="text-3xl font-semibold tracking-tight text-zinc-900 dark:text-white">{{ $plan->formatPrice($plan->price_monthly) }}</span>
                            <span class="text-sm text-zinc-500">/ {{ __('month') }}</span>
                        </div>
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                            @if ($plan->price_yearly !== null)
                                {{ __(':price billed yearly', ['price' => $plan->formatPrice($plan->price_yearly)]) }}
                            @else
                                {{ __('Monthly billing only') }}
                            @endif
                        </p>

                        <ul class="mt-5 space-y-2 text-sm">
                            @foreach (array_slice($plan->featureList(), 0, 4) as $line)
                                <li class="flex items-start gap-2 text-zinc-600 dark:text-zinc-300">
                                    <x-heroicon-s-check-circle class="mt-0.5 size-4 shrink-0 text-primary-500" />
                                    <span class="min-w-0">{{ $line }}</span>
                                </li>
                            @endforeach
                            @if (count($plan->featureList()) > 4)
                                <li class="ps-6 text-xs text-zinc-400">{{ __('+:count more', ['count' => count($plan->featureList()) - 4]) }}</li>
                            @endif
                        </ul>

                        <div class="mt-auto flex flex-wrap items-center gap-2 pt-5">
                            <x-ui.badge>
                                <x-heroicon-m-cursor-arrow-rays class="size-3" />
                                <span class="max-w-40 truncate" dir="ltr">{{ $plan->cta_url ?: __('Sign-up page') }}</span>
                            </x-ui.badge>
                            <x-admin.locale-status :model="$plan" field="name" />
                        </div>
                    </div>

                    <x-admin.content-controls
                        :id="$plan->id"
                        :active="$plan->is_active"
                        :first="$loop->first"
                        :last="$loop->last"
                        :name="$plan->name"
                        class="border-t border-zinc-200/80 px-4 py-2.5 dark:border-white/[0.07]"
                    />
                </x-ui.card>
            @endforeach
        </div>
    @endif

    <x-ui.modal wire:model="showModal" max-width="2xl" icon="banknotes" :title="$editingId ? __('Edit plan') : __('Add plan')">
        <form wire:submit="save" class="space-y-6">
            <div class="grid gap-6 sm:grid-cols-2">
                <x-ui.translatable model="name" :label="__('Name')" :maxlength="40" required />
                <x-ui.translatable model="cta_label" :label="__('Button text')" :maxlength="30" :placeholders="$ctaPlaceholders" />
            </div>

            <x-ui.translatable model="description" :label="__('Description')" :maxlength="160" />

            <x-ui.translatable model="features" type="textarea" rows="5" :label="__('Included')" :description="__('One item per line.')" :maxlength="1500" />

            <div class="grid gap-4 sm:grid-cols-3">
                <x-ui.input wire:model="price_monthly" type="number" min="0" step="0.01" inputmode="decimal" :label="__('Monthly price')" dir="ltr" required />
                <x-ui.input wire:model="price_yearly" type="number" min="0" step="0.01" inputmode="decimal" :label="__('Yearly price')" :description="__('Total for 12 months.')" dir="ltr" />
                <x-ui.select wire:model="currency" :label="__('Currency')">
                    @foreach (Plan::CURRENCIES as $currency)
                        <option value="{{ $currency }}">{{ $currency }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <x-ui.input
                wire:model="cta_url"
                :label="__('Button link')"
                :description="__('Leave empty to send visitors to the sign-up page.')"
                icon="link"
                placeholder="https://… /register #faq"
                dir="ltr"
                autocomplete="off"
            />

            <div class="grid gap-4 rounded-2xl bg-zinc-50 p-4 sm:grid-cols-2 dark:bg-white/[0.03]">
                <x-ui.switch wire:model="is_featured" :label="__('Most popular')" :description="__('Highlight this plan.')" />
                <x-ui.switch wire:model="is_active" :label="__('Visible on the website')" />
            </div>

            <div class="flex justify-end gap-2 border-t border-zinc-200/80 pt-5 dark:border-white/[0.07]">
                <x-ui.button variant="secondary" x-on:click="show = false">{{ __('Cancel') }}</x-ui.button>
                <x-ui.button type="submit" loading="save">{{ __('Save') }}</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
