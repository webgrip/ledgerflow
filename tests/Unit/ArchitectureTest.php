<?php

declare(strict_types=1);

arch('all classes declare strict types')
    ->expect('App')
    ->toUseStrictTypes()
    ->ignoring([
        'App\Livewire',            // starter kit files
        'App\Actions\Fortify',     // Fortify scaffold does not add strict types
        'App\Http\Controllers',    // base controller scaffold
        'App\Concerns',            // Fortify concern traits
        'App\Providers',           // service providers scaffold
    ]);

arch('enums are string-backed enums')
    ->expect('App\Enums')
    ->toBeStringBackedEnum();

arch('actions have a public handle() method')
    ->expect('App\Actions')
    ->toHaveMethod('handle')
    ->ignoring('App\Actions\Fortify'); // Fortify uses reset/create/update naming

arch('models extend the Eloquent base model')
    ->expect('App\Models')
    ->toExtend('Illuminate\Database\Eloquent\Model')
    ->ignoring('App\Models\OrganizationMembership'); // extends Pivot, not Model

arch('actions do not depend on HTTP layer')
    ->expect('App\Actions')
    ->not->toUse('Illuminate\Http\Request')
    ->not->toUse('Illuminate\Http\Response')
    ->ignoring('App\Actions\Fortify');

arch('AI agents use the Promptable trait')
    ->expect('App\Ai\Agents')
    ->toUseTrait('Laravel\Ai\Promptable');
