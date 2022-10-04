<?php

namespace BlackLion\LaravelUtils;

use Throwable;
use Symfony\Component\Mime\Email;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Mime\Part\DataPart;
use Illuminate\Mail\Events\MessageSending;

class EmailLogger
{
    /**
     * Handle the event.
     *
     * @param MessageSending $event
     */
    public function handle(MessageSending $event)
    {
        try {
            $this->newVersion($event);
        } catch (Throwable) {
            try {
                $this->oldVersion($event);
            } catch (Throwable) {
                //
            }
        }
    }

    protected function newVersion(MessageSending $event)
    {
        $formatAddressField = function ($message, $field) {
            $headers = $message->getHeaders();

            return $headers->get($field)?->getBodyAsString();
        };

        $saveAttachments = function (Email $message) {
            if (empty($message->getAttachments())) {
                return null;
            }

            return collect($message->getAttachments())
                ->map(fn(DataPart $part) => $part->toString())
                ->implode("\n\n");
        };

        $message = $event->message;

        DB::table('email_log')->insert([
            'date' => now()->format('Y-m-d H:i:s'),
            'from' => $formatAddressField($message, 'From'),
            'to' => $formatAddressField($message, 'To'),
            'cc' => $formatAddressField($message, 'Cc'),
            'bcc' => $formatAddressField($message, 'Bcc'),
            'subject' => $message->getSubject(),
            'body' => $message->getBody()->bodyToString(),
			'headers' => $message->getHeaders()->toString(),
			'attachments' => $saveAttachments($message),
        ]);
    }

    protected function oldVersion(MessageSending $event)
    {
        $formatAddressField = function ($message, $field) {
            $headers = $message->getHeaders();

            if (! $headers->has($field)) {
                return;
            }

            return collect($headers->get($field)->getFieldBodyModel())
                ->map(function ($name, $email) {
                    if ($name !== null) {
                        return $name.' <'.$email.'>';
                    } else {
                        return $email;
                    }
                })
                ->implode(', ');
        };

        $message = $event->message;

        DB::table('email_log')->insert([
            'date' => date('Y-m-d H:i:s'),
            'from' => $formatAddressField($message, 'From'),
            'to' => $formatAddressField($message, 'To'),
            'cc' => $formatAddressField($message, 'Cc'),
            'bcc' => $formatAddressField($message, 'Bcc'),
            'subject' => $message->getSubject(),
            'body' => $message->getBody(),
            'headers' => (string) $message->getHeaders(),
            'attachments' => $message->getChildren() ? implode("\n\n", $message->getChildren()) : null,
        ]);
    }
}
