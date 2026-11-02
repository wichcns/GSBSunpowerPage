<?php

namespace Tests\Feature\CrmMigrated;

use App\Crm\Http\Middleware\RestoreCrmQueryString;
use Illuminate\Http\Request;
use Tests\TestCase;

class CrmQueryStringRecoveryTest extends TestCase
{
    public function test_crm_get_recovers_query_parameters_from_request_uri_when_php_query_is_empty(): void
    {
        $request = Request::create('/crm/admin/search?q=Bang&view=board', 'GET');
        $request->query->replace([]);
        $request->server->set('QUERY_STRING', '');

        (new RestoreCrmQueryString)->handle($request, function (Request $request) {
            $this->assertSame('Bang', $request->query('q'));
            $this->assertSame('board', $request->query('view'));

            return response('ok');
        });
    }

    public function test_existing_query_values_are_preserved(): void
    {
        $request = Request::create('/crm/admin/search?q=Bang', 'GET');
        $request->query->set('q', 'Current');

        (new RestoreCrmQueryString)->handle($request, function (Request $request) {
            $this->assertSame('Current', $request->query('q'));

            return response('ok');
        });
    }

    public function test_public_website_query_is_not_changed(): void
    {
        $request = Request::create('/blog.html?q=Bang', 'GET');
        $request->query->replace([]);

        (new RestoreCrmQueryString)->handle($request, function (Request $request) {
            $this->assertSame([], $request->query());

            return response('ok');
        });
    }
}
