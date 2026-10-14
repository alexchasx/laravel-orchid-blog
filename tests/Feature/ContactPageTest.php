<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\ConsentLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactPageTest extends TestCase
{
    use RefreshDatabase;

    private const VALID_DATA = [
        'name' => 'Алексей',
        'email' => 'alex@example.com',
        'message' => 'Хочу предложить тему для статьи.',
        'consent_processing' => 1,
    ];

    public function test_contact_page_returns_ok(): void
    {
        $this->get('/contact')->assertOk();
    }

    public function test_store_valid_data_redirects_with_success(): void
    {
        $response = $this->post('/contact.store', self::VALID_DATA);

        $response->assertRedirect(route('contact'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('contacts', [
            'name' => 'Алексей',
            'email' => 'alex@example.com',
            'message' => 'Хочу предложить тему для статьи.',
            'user_id' => null,
        ]);
    }

    public function test_store_valid_data_returns_json_success(): void
    {
        $response = $this->postJson('/contact.store', self::VALID_DATA);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseCount('contacts', 1);
    }

    public function test_store_invalid_data_returns_validation_errors(): void
    {
        $response = $this->post('/contact.store', [
            'email' => 'not-an-email',
            'message' => 'короткое',
        ]);

        $response->assertSessionHasErrors(['name', 'email']);
    }

    public function test_store_with_authenticated_user_sets_user_id(): void
    {
        $user = User::factory()->create(['active' => true]);

        $response = $this->actingAs($user)->post('/contact.store', self::VALID_DATA);

        $response->assertRedirect(route('contact'));
        $this->assertDatabaseHas('contacts', [
            'email' => 'alex@example.com',
            'user_id' => $user->id,
        ]);
    }

    public function test_contact_has_only_fillable_attributes(): void
    {
        $contact = new Contact();

        $this->assertSame([
            'user_id',
            'name',
            'email',
            'title',
            'message',
            'read',
        ], $contact->getFillable());
    }

    public function test_store_without_consent_fails_validation(): void
    {
        $response = $this->post('/contact.store', [
            'name' => 'Алексей',
            'email' => 'alex@example.com',
            'message' => 'Хочу предложить тему для статьи.',
            // consent_processing не отмечен
        ]);

        $response->assertSessionHasErrors('consent_processing');
        $this->assertDatabaseCount('contacts', 0);
    }

    public function test_store_with_consent_creates_consent_log(): void
    {
        $response = $this->post('/contact.store', self::VALID_DATA);

        $response->assertRedirect();

        $contact = Contact::where('email', 'alex@example.com')->first();
        $this->assertNotNull($contact);

        // Проверка consent_logs.
        $this->assertDatabaseHas('consent_logs', [
            'subject_type' => ConsentLog::SUBJECT_CONTACT,
            'subject_id'   => $contact->id,
            'consent_type' => ConsentLog::TYPE_PROCESSING,
        ]);

        $log = ConsentLog::where('subject_type', ConsentLog::SUBJECT_CONTACT)
            ->where('subject_id', $contact->id)
            ->first();

        $this->assertNotNull($log);
        $this->assertStringContainsString('127.0.0.1', $log->ip_address);
        $this->assertNotNull($log->consented_at);
        $this->assertNotNull($log->consent_text);
        $this->assertEquals('1.0', $log->consent_version);
    }
}