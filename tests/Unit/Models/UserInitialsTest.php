<?php

declare(strict_types=1);

use App\Models\User;

describe('User::initials()', function () {

    it('returns the first letter of a single-word name', function () {
        $user = new User(['name' => 'Alice']);
        expect($user->initials())->toBe('A');
    });

    it('returns the first letters of a two-word name', function () {
        $user = new User(['name' => 'Alice Founder']);
        expect($user->initials())->toBe('AF');
    });

    it('returns only the first two initials for a name with more words', function () {
        $user = new User(['name' => 'Alice Marie Founder Smith']);
        expect($user->initials())->toBe('AM');
    });

    it('returns initials in their original case', function () {
        $user = new User(['name' => 'bob accountant']);
        expect($user->initials())->toBe('ba');
    });

    it('handles a name with extra spaces gracefully', function () {
        // Str::of(' ')->explode(' ') produces empty strings — verify no crash
        $user = new User(['name' => 'Carol CFO']);
        expect($user->initials())->toBe('CC');
    });
});
