<?php

namespace BlackLion\LaravelUtils;

use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Email;

class EmailLogger
{
    /**
     * Handle the event.
     *
     * @param MessageSending $event
     */
    public function handle(MessageSending $event)
    {
        $message = $event->message;

        DB::table('email_log')->insert([
            'date' => now()->format('Y-m-d H:i:s'),
            'from' => $this->formatAddressField($message, 'From'),
            'to' => $this->formatAddressField($message, 'To'),
            'cc' => $this->formatAddressField($message, 'Cc'),
            'bcc' => $this->formatAddressField($message, 'Bcc'),
            'subject' => $message->getSubject(),
            'body' => $message->getBody()->bodyToString(),
			'headers' => $message->getHeaders()->toString(),
			'attachments' => $this->saveAttachments($message),
        ]);
    }

    /**
     * Format address strings for sender, to, cc, bcc.
     *
     * @param $message
     * @param $field
     * @return null|string
     */
    protected function formatAddressField($message, $field)
    {
        $headers = $message->getHeaders();

        return $headers->get($field)?->getBodyAsString();
    }

    /**
	 * Collect all attachments and format them as strings.
	 *
	 * @param Email $message
	 * @return string|null
	 */
	protected function saveAttachments(Email $message): ?string
	{
		if (empty($message->getAttachments())) {
			return null;
		}

		return collect($message->getAttachments())
			->map(fn(DataPart $part) => $part->toString())
			->implode("\n\n");
	}
}
