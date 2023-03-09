<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use GeekrOpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class ChatController extends Controller
{
    public function index()
    {
        $messages = collect(session('messages', []))->reject(fn ($message) => $message['role'] === 'system');
        return Inertia::render('Welcome', [
            'messages' => $messages->toArray(),
        ]);
    }

    /**
     * Handle the incoming prompt.
     */
    public function chat(Request $request): RedirectResponse
    {
        $request->validate([
            'prompt' => 'required|string'
        ]);

        $messages = $request->session()->get('messages', [
            ['role' => 'system', 'content' => 'You are GeekChat - A ChatGPT clone. Answer as concisely as possible.']
        ]);

        $messages[] = ['role' => 'user', 'content' => $request->input('prompt')];

        $response = OpenAI::chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages
        ]);

        $messages[] = ['role' => 'assistant', 'content' => $response->choices[0]->message->content];

        $request->session()->put('messages', $messages);

        return Redirect::to('/');
    }

    /**
     * Reset the session.
     */
    public function reset(Request $request): RedirectResponse
    {
        $request->session()->forget('messages');

        return Redirect::to('/');
    }
}
