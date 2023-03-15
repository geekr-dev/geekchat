<?php

namespace App\Http\Controllers;

use App\Facades\OpenAI;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;
use Inertia\Inertia;

class ChatController extends Controller
{
    public function index()
    {
        return Inertia::render('Chat');
    }

    public function messages(): JsonResponse
    {
        $messages = collect(session('messages', []))->reject(fn ($message) => $message['role'] === 'system')->toArray();
        return response()->json(array_values($messages));
    }

    /**
     * Handle the incoming prompt.
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'prompt' => 'required|string'
        ]);

        $messages = $request->session()->get('messages', [
            ['role' => 'system', 'content' => 'You are GeekChat - A ChatGPT clone. Answer as concisely as possible. Using Simplified Chinese as the first language.']
        ]);

        $messages[] = ['role' => 'user', 'content' => $request->input('prompt')];

        try {
            $response = OpenAI::chat([
                'model' => 'gpt-3.5-turbo',
                'messages' => $messages
            ]);
        } catch (Exception $exception) {
            return $this->toJsonResponse($request, SYSTEM_ERROR, $messages);
        }

        $result = json_decode($response);
        $respText = '';
        if (empty($result->choices[0]->message->content)) {
            $respText = '对不起，我没有理解你的意思，请重试';
        } else {
            $respText = $result->choices[0]->message->content;
        }
        return  $this->toJsonResponse($request, $respText, $messages);
    }

    /**
     * Handle the incoming audio prompt.
     */
    public function audio(Request $request): JsonResponse
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
            ['role' => 'system', 'content' => 'You are GeekChat - A ChatGPT clone. Answer as concisely as possible. Make Mandarin Chinese the primary language']
        ]);

        // $path = 'audios/2023/03/09/test.wav';（测试用）
        // 调用 speech to text API 将语音转化为文字
        try {
            $response = OpenAI::transcribe([
                'model' => 'whisper-1',
                'file' => curl_file_create(Storage::disk('local')->path($path)),
                'response_format' => 'verbose_json',
            ]);
        } catch (Exception $exception) {
            return $this->toJsonResponse($request, SYSTEM_ERROR, $messages);
        } finally {
            Storage::disk('local')->delete($path);  // 处理完毕删除音频文件
        }

        $result = json_decode($response);
        if (empty($result->text)) {
            return $this->toJsonResponse($request, '对不起，我没有听清你说的话，请再试一次', $messages);
        }

        // 接下来的流程和 ChatGPT 一样
        $reqMessage = ['role' => 'user', 'content' => $result->text];
        $messages[] = $reqMessage;
        try {
            $response = OpenAI::chat([
                'model' => 'gpt-3.5-turbo',
                'messages' => $messages
            ]);
        } catch (Exception $exception) {
            return $this->toJsonResponse($request, SYSTEM_ERROR, $messages, true);
        }

        $result = json_decode($response);
        $respText = '';
        if (empty($result->choices[0]->message->content)) {
            $respText = '对不起，我没有听明白你的意思，请再说一遍';
        } else {
            $respText = $result->choices[0]->message->content;
        }
        return $this->toJsonResponse($request, $respText, $messages, true);
    }

    /**
     * Reset the session.
     */
    public function reset(Request $request): JsonResponse
    {
        $request->session()->forget('messages');

        return response()->json(['status' => 'ok']);
    }

    private function toJsonResponse($request, $message, $messages, $isAudio = false): JsonResponse
    {
        $respMessage = ['role' => 'assistant', 'content' => $message];
        $messages[] = $respMessage;
        $request->session()->put('messages', $messages);
        if (!$isAudio) {
            return response()->json($respMessage);
        }
        // 语音模式下，返回最后两条消息（第一条是语音解析出来的文本，第二条是机器人的回复）
        $chatMessage = array_slice($messages, -2);
        return response()->json($chatMessage);
    }
}
