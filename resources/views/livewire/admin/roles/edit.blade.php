<div>
    <x-ui.page-header
        :title="__('Edit role')"
        :description="$role->name"
        :back="route('admin.roles.index')"
        :back-label="__('Roles')"
    />

    <div class="max-w-4xl">
        @include('livewire.admin.roles.partials.form', ['renameLocked' => \App\Enums\SystemRole::isProtectedName($role->name)])
    </div>
</div>
