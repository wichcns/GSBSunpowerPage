<?php

namespace Tests\Feature\CrmMigrated;

use Illuminate\Contracts\Auth\Authenticatable;
use Tests\TestCase;

abstract class CrmTestCase extends TestCase
{
    public function actingAs(Authenticatable $user, $guard = null)
    {
        return parent::actingAs($user, $guard ?? 'crm');
    }
}
