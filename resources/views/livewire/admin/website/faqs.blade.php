<div>
    <x-ui.page-header :eyebrow="__('Website')" :title="__('Website FAQ')" :description="__('Questions and answers at the bottom of the website. The first one starts open.')">
        <x-slot:actions>
            <x-ui.button icon="plus" wire:click="create">{{ __('Add question') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-admin.website-nav />

    <x-ui.card :padding="false">
        @forelse ($this->faqs as $faq)
            <div wire:key="faq-{{ $faq->id }}" @class([
                'flex flex-col gap-4 px-5 py-4 lg:flex-row lg:items-center',
                'border-t border-zinc-200/80 dark:border-white/[0.07]' => ! $loop->first,
                'opacity-60' => ! $faq->is_active,
            ])>
                <span class="hidden size-9 shrink-0 items-center justify-center rounded-xl bg-zinc-100 font-mono text-xs font-semibold text-zinc-500 lg:flex dark:bg-white/5 dark:text-zinc-400" dir="ltr">{{ $loop->iteration }}</span>

                <div class="min-w-0 flex-1">
                    <h2 class="font-medium text-zinc-900 dark:text-white">{{ $faq->question }}</h2>
                    <p class="mt-1 line-clamp-2 text-sm text-zinc-500 dark:text-zinc-400">{{ $faq->answer }}</p>
                    <x-admin.locale-status :model="$faq" field="question" class="mt-2" />
                </div>

                <x-admin.content-controls
                    :id="$faq->id"
                    :active="$faq->is_active"
                    :first="$loop->first"
                    :last="$loop->last"
                    :name="$faq->question"
                    class="shrink-0 lg:w-[22rem]"
                />
            </div>
        @empty
            <x-ui.empty-state icon="question-mark-circle" :title="__('No questions yet')" :description="__('Add a question. The FAQ section stays hidden on the website until there is one.')">
                <x-ui.button icon="plus" wire:click="create">{{ __('Add question') }}</x-ui.button>
            </x-ui.empty-state>
        @endforelse
    </x-ui.card>

    <x-ui.modal wire:model="showModal" max-width="xl" icon="question-mark-circle" :title="$editingId ? __('Edit question') : __('Add question')">
        <form wire:submit="save" class="space-y-6">
            <x-ui.translatable model="question" :label="__('Question')" :maxlength="200" required />
            <x-ui.translatable model="answer" type="textarea" rows="5" :label="__('Answer')" :maxlength="2000" required />
            <x-ui.switch wire:model="is_active" :label="__('Visible on the website')" />

            <div class="flex justify-end gap-2 border-t border-zinc-200/80 pt-5 dark:border-white/[0.07]">
                <x-ui.button variant="secondary" x-on:click="show = false">{{ __('Cancel') }}</x-ui.button>
                <x-ui.button type="submit" loading="save">{{ __('Save') }}</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
