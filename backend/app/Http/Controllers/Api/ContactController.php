<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactRequest;
use App\Mail\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class ContactController extends Controller
{
    public function __invoke(StoreContactRequest $request): JsonResponse
    {
        $recipient = config('services.portfolio_contact_email');
        $mailer = config('mail.default');

        if (! is_string($recipient) || $recipient === '' || in_array($mailer, ['log', 'array'], true)) {
            return response()->json([
                'message' => 'Contact delivery is not configured yet. Please use the email link instead.',
            ], 503);
        }

        $contact = $request->safe()->only(['name', 'email', 'subject', 'message']);

        try {
            Mail::to($recipient)->send(new ContactMessage(
                senderName: $contact['name'],
                senderEmail: $contact['email'],
                subjectLine: $contact['subject'],
                messageBody: $contact['message'],
            ));
        } catch (TransportExceptionInterface $exception) {
            Log::error('Portfolio contact email delivery failed.', [
                'exception' => $exception::class,
            ]);

            return response()->json([
                'message' => 'Your message could not be sent right now. Please try again later or email me directly.',
            ], 503);
        }

        return response()->json(['message' => 'Your message was sent.']);
    }
}
