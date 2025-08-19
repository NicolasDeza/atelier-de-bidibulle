<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mail\ContactMail;
use Illuminate\Support\Facades\Mail;


class TestMailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-mail-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Mail::to('test@example.com')->send(
        new ContactMail('Jean Dupont', 'jean@example.com', 'Test via artisan command 🚀')
    );

    $this->info('Mail envoyé avec succès !');
    }
}
