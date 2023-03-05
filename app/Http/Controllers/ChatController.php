<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;

class ChatController extends Controller
{
    /**
     * Handle the incoming prompt.
     */
    public function chat(Request $request): RedirectResponse
    {
        $messages = $request->session()->get('messages', [
            ['role' => 'system', 'content' => 'You are LaravelGPT - A ChatGPT clone. Answer as concisely as possible.']
        ]);

        $messages[] = ['role' => 'user', 'content' => $request->input('message')];

        $response = OpenAI::chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages
        ]);

        $messages[] = ['role' => 'assistant', 'content' => $response->choices[0]->message->content];

        $request->session()->put('messages', $messages);

        return redirect('/');
    }

    /**
     * Reset the session.
     */
    public function reset(Request $request): RedirectResponse
    {
        $request->session()->forget('messages');

        return redirect('/');
    }
}
