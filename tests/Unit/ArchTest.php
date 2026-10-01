<?php

use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use Livewire\Form;

arch('debugging helpers are not left behind')
    ->expect(['dd', 'dump', 'ddd', 'ray', 'var_dump', 'print_r'])
    ->not->toBeUsed();

arch('models extend Eloquent')
    ->expect('App\Models')
    ->classes()
    ->toExtend(Model::class);

arch('enums are backed enums')
    ->expect('App\Enums')
    ->toBeStringBackedEnums();

arch('livewire components extend the Livewire base component')
    ->expect('App\Livewire')
    ->classes()
    ->toExtend(Component::class)
    ->ignoring(['App\Livewire\Actions', 'App\Livewire\Concerns', 'App\Livewire\Forms']);

arch('form objects extend the Livewire form')
    ->expect('App\Livewire\Forms')
    ->toExtend(Form::class);

arch('policies are suffixed')
    ->expect('App\Policies')
    ->toHaveSuffix('Policy');
