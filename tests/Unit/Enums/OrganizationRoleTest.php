<?php

declare(strict_types=1);

use App\Enums\OrganizationRole;

describe('OrganizationRole enum', function () {

    it('has owner and member as the only two cases', function () {
        expect(OrganizationRole::cases())->toHaveCount(2);
    });

    it('has the correct backing values', function () {
        expect(OrganizationRole::Owner->value)->toBe('owner')
            ->and(OrganizationRole::Member->value)->toBe('member');
    });

    it('owner and member are distinct', function () {
        expect(OrganizationRole::Owner)->not->toBe(OrganizationRole::Member);
    });

    it('can be round-tripped through from() and value', function () {
        expect(OrganizationRole::from('owner'))->toBe(OrganizationRole::Owner)
            ->and(OrganizationRole::from('member'))->toBe(OrganizationRole::Member);
    });
});
