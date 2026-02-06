<?php

namespace Tests\Unit;

use App\Http\Responses\ApiResponse;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ApiResponseTest extends TestCase
{
    public function test_success_returns_json_with_data(): void
    {
        $response = ApiResponse::success(['id' => 1], 'OK', 200);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertSame('OK', $data['message']);
        $this->assertSame(['id' => 1], $data['data']);
    }

    public function test_success_without_data_omits_data_key(): void
    {
        $response = ApiResponse::success(null, 'Done');

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertArrayNotHasKey('data', $data);
    }

    public function test_success_default_status_code_is_200(): void
    {
        $response = ApiResponse::success([]);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_success_accepts_201(): void
    {
        $response = ApiResponse::success([], 'Created', 201);
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function test_error_returns_json_with_message(): void
    {
        $response = ApiResponse::error('Something failed', null, 400);

        $this->assertEquals(400, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertSame('Something failed', $data['message']);
    }

    public function test_error_with_errors_array_includes_errors(): void
    {
        $errors = ['email' => ['Email is required.']];
        $response = ApiResponse::error('Validation failed', $errors, 422);

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('errors', $data);
        $this->assertSame($errors, $data['errors']);
    }

    public function test_validation_error_returns_422(): void
    {
        $response = ApiResponse::validationError('Invalid.', ['field' => ['Error']]);

        $this->assertEquals(422, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertSame('Invalid.', $data['message']);
    }

    public function test_from_validation_exception_returns_422_with_errors(): void
    {
        $e = ValidationException::withMessages(['email' => ['Required.']]);
        $response = ApiResponse::fromValidationException($e);

        $this->assertEquals(422, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertSame(['email' => ['Required.']], $data['errors']);
    }
}
