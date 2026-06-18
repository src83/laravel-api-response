<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Tests\Feature;

use Illuminate\Http\Request;
use Src83\LaravelApiResponse\Tests\TestCase;

final class RequestApiModuleTest extends TestCase
{
    /** @test */
    public function it_detects_api_module_from_path(): void
    {
        $request = Request::create('/api', 'GET');
        $this->assertNull($request->apiModule());

        $request = Request::create('/api/users/123/edit', 'GET');
        $this->assertSame('users_edit', $request->apiModule());

        $request = Request::create('/api/admin/roles', 'GET');
        $this->assertSame('admin_roles', $request->apiModule());

        $request = Request::create('/web/home', 'GET');
        $this->assertNull($request->apiModule());
    }
}
