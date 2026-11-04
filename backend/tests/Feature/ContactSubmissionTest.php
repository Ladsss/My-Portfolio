<?php

namespace Tests\Feature;

use App\Mail\ContactMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class ContactSubmissionTest extends TestCase
{
    public function test_valid_contact_request_sends_a_message_to_the_configured_recipient(): void
    {
        Mail::fake();
        config(['services.portfolio_contact_email' => 'owner@example.com']);
        config(['mail.default' => 'smtp']);

        $response = $this->postJson('/api/contact', [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'subject' => 'Project inquiry',
            'message' => 'I would like to discuss a project.',
            'website' => '',
        ]);

        $response->assertOk()->assertJson(['message' => 'Your message was sent.']);
        Mail::assertSent(ContactMessage::class, function (ContactMessage $mail): bool {
            return $mail->hasTo('owner@example.com')
                && $mail->hasReplyTo('jane@example.com', 'Jane Smith')
                && $mail->envelope()->subject === 'Project inquiry'
                && $mail->messageBody === 'I would like to discuss a project.';
        });
    }

    public function test_contact_message_renders_readable_html_and_plain_text_versions(): void
    {
        $mail = new ContactMessage(
            senderName: 'Jane <Smith>',
            senderEmail: 'jane@example.com',
            subjectLine: 'Project inquiry',
            messageBody: "First line\n<script>alert('x')</script>",
        );

        $html = $mail->render();
        $content = $mail->content();
        $text = view($content->text, [
            'senderName' => $mail->senderName,
            'senderEmail' => $mail->senderEmail,
            'subjectLine' => $mail->subjectLine,
            'messageBody' => $mail->messageBody,
        ])->render();

        $this->assertSame('emails.contact-message-text', $content->text);
        $this->assertStringContainsString('Jane &lt;Smith&gt;', $html);
        $this->assertStringContainsString('jane@example.com', $html);
        $this->assertStringContainsString('Email Address', $html);
        $this->assertStringContainsString('Project inquiry', $html);
        $this->assertStringContainsString('First line<br />', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(&#039;x&#039;)&lt;/script&gt;', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('Name: Jane &lt;Smith&gt;', $text);
        $this->assertStringContainsString('Subject: Project inquiry', $text);
        $this->assertStringContainsString("First line\n&lt;script&gt;alert(&#039;x&#039;)&lt;/script&gt;", $text);
    }

    public function test_contact_request_reports_unavailable_delivery_when_no_recipient_is_configured(): void
    {
        Mail::fake();
        config(['services.portfolio_contact_email' => null]);

        $response = $this->postJson('/api/v1/contact', [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'subject' => 'Project inquiry',
            'message' => 'I would like to discuss a project.',
        ]);

        $response->assertServiceUnavailable()->assertJson([
            'message' => 'Contact delivery is not configured yet. Please use the email link instead.',
        ]);
        Mail::assertNothingOutgoing();
    }

    public function test_contact_request_does_not_report_success_for_the_log_mailer(): void
    {
        Mail::fake();
        config(['services.portfolio_contact_email' => 'owner@example.com']);
        config(['mail.default' => 'log']);

        $response = $this->postJson('/api/v1/contact', [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'subject' => 'Project inquiry',
            'message' => 'I would like to discuss a project.',
        ]);

        $response->assertServiceUnavailable()->assertJson([
            'message' => 'Contact delivery is not configured yet. Please use the email link instead.',
        ]);
        Mail::assertNothingOutgoing();
    }

    public function test_contact_request_reports_a_clear_error_when_smtp_delivery_fails(): void
    {
        config(['services.portfolio_contact_email' => 'owner@example.com']);
        config(['mail.default' => 'smtp']);
        Mail::shouldReceive('to')
            ->once()
            ->with('owner@example.com')
            ->andThrow(new TransportException('SMTP unavailable'));
        Log::shouldReceive('error')
            ->once()
            ->with('Portfolio contact email delivery failed.', [
                'exception' => TransportException::class,
            ]);

        $response = $this->postJson('/api/contact', [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'subject' => 'Project inquiry',
            'message' => 'I would like to discuss a project.',
        ]);

        $response->assertServiceUnavailable()->assertJson([
            'message' => 'Your message could not be sent right now. Please try again later or email me directly.',
        ]);
    }

    public function test_empty_contact_request_returns_field_validation_errors(): void
    {
        $response = $this->postJson('/api/v1/contact', []);

        $response->assertUnprocessable()->assertJsonValidationErrors(['name', 'email', 'subject', 'message']);
    }

    public function test_contact_request_rejects_a_filled_honeypot(): void
    {
        $response = $this->postJson('/api/v1/contact', [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'subject' => 'Project inquiry',
            'message' => 'I would like to discuss a project.',
            'website' => 'https://spam.example',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['website']);
    }

    public function test_contact_request_is_rate_limited_after_five_submissions_per_minute(): void
    {
        $payload = [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'subject' => 'Project inquiry',
            'message' => 'I would like to discuss a project.',
        ];

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $response = $this->postJson('/api/v1/contact', $payload);
        }

        $response->assertServiceUnavailable();
        $limitedResponse = $this->postJson('/api/v1/contact', $payload);

        $limitedResponse->assertTooManyRequests();
    }
}
