<?php

namespace App\Http\Controllers;

use App\Facades\OpenAI;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;
use Inertia\Inertia;

class ChatController extends Controller
{
    private $preset = ['role' => 'system', 'content' => 'You are GeekChat - A chatbot that can understand text, voice, draw image and translate. Answer as concisely as possible. Using Simplified Chinese as the first language.'];

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
            'prompt' => 'required|string',
            'regen' => ['required', 'in:true,false'],
            'api_key' => 'sometimes|string',
        ]);
        $regen = $request->boolean('regen');
        $messages = $request->session()->get('messages', [$this->preset]);
        if ($regen && count($messages) == 1) {
            // 处理服务端session已过期的情况
            $regen = false;
        }
        $userMessage = ['role' => 'user', 'content' => $request->input('prompt')];
        if (!$regen) {
            // 基于session存储当前会话信息
            $messages[] = $userMessage;
            $request->session()->put('messages', $messages);
        } elseif ($messages[count($messages) - 1]['role'] == 'assistant') {
            // 重试则删除最后一条回复
            array_pop($messages);
            $request->session()->put('messages', $messages);
        }
        $chatId = Str::uuid(); // 生成一个唯一聊天ID作为下次请求的凭证
        $request->session()->put('chat_id', $chatId);
        return response()->json(['chat_id' => $chatId, 'message' => $userMessage]);
    }

    /**
     * Handle the incoming prompt.
     */
    public function translate(Request $request): JsonResponse
    {
        $request->validate([
            'prompt' => 'required|string',
            'regen' => ['required', 'in:true,false'],
            'api_key' => 'sometimes|string',
        ]);
        $regen = $request->boolean('regen');
        $messages = $request->session()->get('messages', [$this->preset]);
        if ($regen && count($messages) == 1) {
            $regen = false;
        }
        // 判断内容是中文还是英文
        $isChinese = preg_match('/[\x{4e00}-\x{9fa5}]/u', $request->input('prompt'));
        $prefix = $isChinese ? '请将以下内容翻译成英文: ' : '请将以下内容翻译成中文: ';
        $userMessage = ['role' => 'user', 'content' => $prefix . $request->input('prompt')];
        if (!$regen) {
            $messages[] = $userMessage;
            $request->session()->put('messages', $messages); // 基于session存储当前会话信息
        } elseif ($messages[count($messages) - 1]['role'] == 'assistant') {
            array_pop($messages);
            $request->session()->put('messages', $messages);
        }
        $chatId = Str::uuid();
        $request->session()->put('chat_id', $chatId);
        return response()->json(['chat_id' => $chatId, 'message' => $userMessage]);
    }

    /**
     * Handle the stream response.
     */
    public function stream(Request $request)
    {
        // 校验请求是否合法
        if ($request->session()->get('chat_id') != $request->input('chat_id')) {
            abort(400);
        }

        $messages = $request->session()->get('messages');

        $params = [
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages,
            'stream' => true,
        ];

        // 实时将流式响应数据发送到客户端
        $respData = '';
        $apiKey = $request->input('api_key');
        if ($apiKey) {
            $apiKey = base64_decode($apiKey);
        }
        header('Access-Control-Allow-Origin: *');
        header('Content-type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');
        OpenAI::withToken($apiKey)->chat($params, function ($ch, $data) use ($apiKey, &$respData) {
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode >= 400) {
                echo "data: [ERROR] $httpCode";
                if (($httpCode == 400 || $httpCode == 401) && empty($apiKey)) {
                    // app key 耗尽自动切换到下一个免费的 key
                    Artisan::call('app:update-open-ai-key');
                }
            } else {
                $respData .= $data;
                echo $data;;
            }
            ob_flush();
            flush();
            return strlen($data);
        });

        // 将响应数据存储到当前会话中以便刷新页面后可以看到聊天记录
        if (!empty($respData)) {
            $lines = explode("\n\n", $respData);
            $respText = '';
            foreach ($lines as $line) {
                $data = substr($line, 5); // 每行数据结构是 data: {...}
                if ($data === '[DONE]') {
                    break;
                } else {
                    $segment = json_decode($data);
                    if (isset($segment->choices[0]->delta->content)) {
                        $respText .= $segment->choices[0]->delta->content;
                    }
                }
            }
            $messages[] = ['role' => 'assistant', 'content' => $respText];
            $request->session()->put('messages', $messages);
        }
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
            ],
            'api_key' => 'sometimes|string',
        ]);

        // 保存到本地
        $fileName = Str::uuid() . '.wav';
        $dir = 'audios' . date('/Y/m/d', time());
        $path = $request->audio->storeAs($dir, $fileName, 'local');

        $messages = $request->session()->get('messages', [
            ['role' => 'system', 'content' => 'You are GeekChat - A chatbot that can understand text, voice, draw image and translate. Answer as concisely as possible. Make Mandarin Chinese the primary language']
        ]);
        // $path = 'audios/2023/03/09/test.wav';（测试用）
        // 调用 speech to text API 将语音转化为文字
        try {
            $response = OpenAI::withToken($request->input('api_key'))->transcribe([
                'model' => 'whisper-1',
                'file' => curl_file_create(Storage::disk('local')->path($path)),
                'response_format' => 'verbose_json',
            ]);
        } catch (Exception $exception) {
            return response()->json(['message' => ['role' => 'assistant', 'content' => SYSTEM_ERROR]]);
        } finally {
            Storage::disk('local')->delete($path);  // 处理完毕删除音频文件
        }
        $result = json_decode($response);
        if (empty($result->text)) {
            if ($request->input('api_key')) {
                return response()->json(['message' => ['role' => 'assistant', 'content' => '识别语音失败，请确保你的KEY有效']]);
            }
            return response()->json(['message' => ['role' => 'assistant', 'content' => '对不起，我没有听清你说的话，请再试一次']]);
        }

        // 接下来的流程和 ChatGPT 一样
        $userMessage = ['role' => 'user', 'content' => $result->text];
        $messages[] = $userMessage;
        $request->session()->put('messages', $messages);
        $chatId = Str::uuid();
        $request->session()->put('chat_id', $chatId);
        return response()->json(['chat_id' => $chatId, 'message' => $userMessage]); // 将语音识别结果先返回给客户端
    }

    public function image(Request $request): JsonResponse
    {
        $request->validate([
            'prompt' => 'required|string',
            'regen' => ['required', 'in:true,false'],
            'api_key' => 'sometimes|string',
        ]);
        $regen = $request->boolean('regen');
        $messages = $request->session()->get('messages', [$this->preset]);
        if ($regen && count($messages) == 1) {
            $regen = false;
        }
        $prompt = $request->input('prompt');
        $userMsg = ['role' => 'user', 'content' => $prompt];
        if (!$regen) {
            $messages[] = $userMsg;
        } elseif ($messages[count($messages) - 1]['role'] == 'assistant') {
            array_pop($messages);
        }
        $apiKey = $request->input('api_key');
        $size = '256x256';
        if (!empty($apiKey)) {
            $size = '1024x1024';
        }
        $response = OpenAI::withToken($apiKey)->image([
            "prompt" => $prompt,
            "n" => 1,
            "size" => $size,
            "response_format" => "url",
        ]);
        $result = json_decode($response);
        $image = '画图失败，如果你设置了自己的key，请确保它是有效的';
        if (isset($result->data[0]->url)) {
            $image = '![](' . $result->data[0]->url . ')';
        }
        $assistantMsg = ['role' => 'assistant', 'content' => $image];
        $messages[] = $assistantMsg;
        $request->session()->put('messages', $messages);
        return response()->json($assistantMsg);
    }

    /**
     * Reset the session.
     */
    public function reset(Request $request): JsonResponse
    {
        $request->session()->forget('messages');

        return response()->json(['status' => 'ok']);
    }

    public function valid(Request $request): JsonResponse
    {
        $request->validate([
            'api_key' => 'required|string'
        ]);
        $apiKey = $request->input('api_key');
        if (empty($apiKey)) {
            return response()->json(['valid' => false, 'error' => '无效的 API KEY']);
        }
        $response = Http::withToken($apiKey)->timeout(15)
            ->get(config('openai.base_uri') . '/dashboard/billing/credit_grants');
        if ($response->failed()) {
            return response()->json(['valid' => false, 'error' => '无效的 API KEY']);
        }
        return response()->json(['valid' => true]);
    }
}
