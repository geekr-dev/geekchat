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
    private $preset = ['role' => 'system', 'content' => 'You are GeekChat - A ChatGPT clone. Answer as concisely as possible. Using English as the first language.'];

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
            // Handling server-side session expiration
            $regen = false;
        }
        $userMessage = ['role' => 'user', 'content' => $request->input('prompt')];
        if (!$regen) {
            // Store current session info based on session.
            $messages[] = $userMessage;
            $request->session()->put('messages', $messages);
        } elseif ($messages[count($messages) - 1]['role'] == 'assistant') {
            // Delete the last reply if retrying.
            array_pop($messages);
            $request->session()->put('messages', $messages);
        }
        $chatId = Str::uuid(); // Generate a unique chat ID as a credential for the next request.
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
        // Determine whether the content is in Chinese or English.
        $isChinese = preg_match('/[\x{4e00}-\x{9fa5}]/u', $request->input('prompt'));
        $prefix = $isChinese ? 'Please translate the following content into English: ' : 'Please translate the following content into Chinese:';
        $userMessage = ['role' => 'user', 'content' => $prefix . $request->input('prompt')];
        if (!$regen) {
            $messages[] = $userMessage;
            $request->session()->put('messages', $messages);
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
        // Validate if the request is legitimate.
        if ($request->session()->get('chat_id') != $request->input('chat_id')) {
            abort(400);
        }

        $messages = $request->session()->get('messages');

        $params = [
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages,
            'stream' => true,
        ];

        // Real-time streaming response data sent to the client
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
                    // When the app key is exhausted, switch to the next free key automatically
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

        // Store response data in the current session so that chat history can be seen after refreshing the page
        if (!empty($respData)) {
            $lines = explode("\n\n", $respData);
            $respText = '';
            foreach ($lines as $line) {
                $data = substr($line, 5); // The structure of each line of data is data: {...}.
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
                    ->min(1)  // Minimum is not less than 1 KB
                    ->max(10 * 1024), // Maximum not more than 10 MB
            ],
            'api_key' => 'sometimes|string',
        ]);

        // Save to local
        $fileName = Str::uuid() . '.wav';
        $dir = 'audios' . date('/Y/m/d', time());
        $path = $request->audio->storeAs($dir, $fileName, 'local');

        $messages = $request->session()->get('messages', [
            ['role' => 'system', 'content' => 'You are GeekChat - A ChatGPT clone. Answer as concisely as possible. Make American English as the primary language']
        ]);
        // $path = 'audios/2023/03/09/test.wav';（for test）
        // Call the speech to text API to convert speech to text
        try {
            $response = OpenAI::withToken($request->input('api_key'))->transcribe([
                'model' => 'whisper-1',
                'file' => curl_file_create(Storage::disk('local')->path($path)),
                'response_format' => 'verbose_json',
            ]);
        } catch (Exception $exception) {
            return response()->json(['message' => ['role' => 'assistant', 'content' => SYSTEM_ERROR]]);
        } finally {
            Storage::disk('local')->delete($path);  // Delete the audio file once processing is completed
        }
        $result = json_decode($response);
        if (empty($result->text)) {
            if ($request->input('api_key')) {
                return response()->json(['message' => ['role' => 'assistant', 'content' => 'Speech recognition failed, please make sure your KEY is valid.']]);
            }
            return response()->json(['message' => ['role' => 'assistant', 'content' => 'I\'m sorry, I didn\'t hear what you said, please try again.']]);
        }

        // The following process is the same as ChatGPT API
        $userMessage = ['role' => 'user', 'content' => $result->text];
        $messages[] = $userMessage;
        $request->session()->put('messages', $messages);
        $chatId = Str::uuid();
        $request->session()->put('chat_id', $chatId);
        // Return the speech recognition result to the client first
        return response()->json(['chat_id' => $chatId, 'message' => $userMessage]);
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
        $image = 'Failed to draw, if you set your own KEY, please make sure it is valid.';
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
            return response()->json(['valid' => false, 'error' => 'invalid API KEY']);
        }
        $response = Http::withToken($apiKey)->timeout(15)
            ->get(config('openai.base_uri') . '/dashboard/billing/credit_grants');
        if ($response->failed()) {
            return response()->json(['valid' => false, 'error' => 'invalid API KEY']);
        }
        return response()->json(['valid' => true]);
    }
}
