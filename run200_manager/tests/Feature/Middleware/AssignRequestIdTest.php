<?php

declare(strict_types=1);

namespace Tests\Feature\Middleware;

use App\Http\Middleware\AssignRequestId;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignRequestIdTest extends TestCase
{
    use RefreshDatabase;

    public function test_response_contains_request_id_header(): void
    {
        $response = $this->get('/');

        $response->assertHeader(AssignRequestId::HEADER_NAME);

        $requestId = $response->headers->get(AssignRequestId::HEADER_NAME);
        $this->assertNotEmpty($requestId);
    }

    public function test_request_id_is_ulid_format(): void
    {
        $response = $this->get('/');

        $requestId = $response->headers->get(AssignRequestId::HEADER_NAME);

        // ULID is 26 characters
        $this->assertEquals(26, strlen($requestId));
    }

    public function test_preserves_client_provided_request_id(): void
    {
        $clientRequestId = 'client-request-123456';

        $response = $this->withHeader(AssignRequestId::HEADER_NAME, $clientRequestId)
            ->get('/');

        $response->assertHeader(AssignRequestId::HEADER_NAME, $clientRequestId);
    }

    public function test_each_request_gets_unique_id(): void
    {
        $response1 = $this->get('/');
        $response2 = $this->get('/');

        $id1 = $response1->headers->get(AssignRequestId::HEADER_NAME);
        $id2 = $response2->headers->get(AssignRequestId::HEADER_NAME);

        $this->assertNotEquals($id1, $id2);
    }

    public function test_request_id_available_on_authenticated_routes(): void
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertHeader(AssignRequestId::HEADER_NAME);
    }
}
