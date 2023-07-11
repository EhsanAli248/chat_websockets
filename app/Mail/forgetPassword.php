<?php

namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

        class forgetPassword extends Mailable
        {
            use Queueable, SerializesModels;

            public $token;

            /**
             * Create a new message instance.
             *
             * @param  string  $token
             * @return void
             */
            public function __construct($token)
            {
                $this->token = $token;
            }

            /**
             * Build the message.
             *
             * @return $this
             */
            public function build()
            {
                return $this->subject('Forget Password')
                    ->view('forgetPassword')
                    ->with([
                        'token' => $this->token,
                    ]);
            }
        }
