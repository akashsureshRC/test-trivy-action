<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommonEmailTemplate extends Mailable
{
    use Queueable, SerializesModels;
    public $template;
    public $user_id;
    public $workspace_id;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($template,$user_id,$workspace_id)
    {
        $this->template = $template;
        $this->user_id = $user_id;
        $this->workspace_id = $workspace_id;
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $fromAddress = companySetting('mail_from_address', $this->user_id, $this->workspace_id) 
            ?? config('mail.from.address');
        $fromName = $this->template->from ?? config('mail.from.name');

        return  $this->from($fromAddress, $fromName)
                ->view('email.common_email_template')
                ->subject($this->template->subject)
                ->with('content', $this->template->content);
    }
}
