<div>
    <x-ui.page-header
        :title="__('Create user')"
        :description="__('Add a new account and choose what it can access.')"
        :back="route('admin.users.index')"
        :back-label="__('Users')"
    />

    <div class="max-w-3xl">
        @include('livewire.admin.users.partials.form', ['editing' => false])
    </div>
</div>
