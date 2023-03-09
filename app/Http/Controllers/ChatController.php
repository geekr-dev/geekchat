<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use GeekrOpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;
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

        $respText = '';
        if (empty($response->choices[0]->message->content)) {
            $respText = '对不起，我没有理解你的意思，请重试';
        } else {
            $respText = $response->choices[0]->message->content;
        }

        $messages[] = ['role' => 'assistant', 'content' => $respText];

        $request->session()->put('messages', $messages);

        return Redirect::to('/');
    }

    /**
     * Handle the incoming audio prompt.
     */
    public function audio(Request $request): RedirectResponse
    {
        $request->validate([
            'audio' => [
                'required',
                File::types(['mp3', 'mp4', 'mpeg', 'mpga', 'm4a', 'wav', 'webm'])
                    ->min(1)  // 最小不低于 1 KB
                    ->max(10 * 1024), // 最大不超过 10 MB
            ]
        ]);

        // 保存到本地
        $fileName = Str::uuid() . '.wav';
        $dir = 'audios' . date('/Y/m/d', time());
        $path = $request->audio->storeAs($dir, $fileName, 'local');

        $messages = $request->session()->get('messages', [
            ['role' => 'system', 'content' => 'You are GeekChat - A ChatGPT clone. Answer as concisely as possible. 把简体中文作为第一语言']
        ]);

        // $path = 'audios/2023/03/09/test.wav';（测试用）
        // 调用 speech to text API 将语音转化为文字
        $response = OpenAI::audio()->transcribe([
            'model' => 'whisper-1',
            'file' => fopen(Storage::disk('local')->path($path), 'r'),
            'response_format' => 'verbose_json',
        ]);

        if (empty($response->text)) {
            $messages[] = ['role' => 'system', 'content' => '对不起，我没有听清你说的话，请再试一次'];
            $request->session()->put('messages', $messages);
            return Redirect::to('/');
        }

        // 接下来的流程和 ChatGPT 一样
        $messages[] = ['role' => 'user', 'content' => $response->text];

        $response = OpenAI::chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages
        ]);

        $respText = '';
        if (empty($response->choices[0]->message->content)) {
            $respText = '对不起，我没有听明白你的意思，请再说一遍';
        } else {
            $respText = $response->choices[0]->message->content;
        }
        $messages[] = ['role' => 'assistant', 'content' => $respText];

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
