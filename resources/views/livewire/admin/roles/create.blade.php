<div>
    <x-ui.page-header
        :title="__('Create role')"
        :description="__('Give the role a name and pick its permissions.')"
        :back="route('admin.roles.index')"
        :back-label="__('Roles')"
    />

    <div class="max-w-4xl">
        @include('livewire.admin.roles.partials.form', ['renameLocked' => false])
    </div>
</div>
