<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class UpdateOpenAiKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-open-ai-key';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Updating OpenAI key...');
        $response = Http::withToken(config('openai.api_key'))->timeout(15)
            ->get(config('openai.base_uri') . '/dashboard/billing/credit_grants');
        $amount = $response->json('total_available');
        if ($amount > 0) {
            $this->info('Current key is still valid.');
            return;
        }
        $keys = file(base_path('openai_keys'));
        $new_key = trim(array_shift($keys), "\n");
        if (!$new_key) {
            $this->info('No more keys.');
            return;
        }
        file_put_contents(base_path('openai_keys'), implode('', $keys));
        $origin = file_get_contents(base_path('.env'));
        $old_key = config('openai.api_key');
        $result = str_replace($old_key, $new_key, $origin);
        file_put_contents(base_path('.env'), $result);
        $this->call('config:clear');
        $this->call('config:cache');
        $this->info('OpenAI key updated.');
    }
}
