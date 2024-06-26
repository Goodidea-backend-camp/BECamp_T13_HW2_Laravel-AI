<?php

namespace App\AI;

use OpenAI;
use OpenAI\Client;
use OpenAI\Responses\Threads\Runs\ThreadRunResponse;

/**
 * @note 這個 Class 還在測試階段，請不要使用或更動
 *
 * @phpstan-consistent-constructor
 */
class LaraparseAssistant
{
    public Client $client;

    /**
     * The OpenAI AssistantResponse instance.
     */
    protected OpenAI\Responses\Assistants\AssistantResponse $assistant;

    /**
     * The id of the current thread.
     */
    protected string $threadId;

    public function __construct(string $assistantId)
    {
        $this->client = OpenAI::client(config('services.openai.api_key'));
        $this->assistant = $this->client->assistants()->retrieve($assistantId);
    }

    /**
     * Create a new OpenAI Assistant.
     */
    public function create(array $config): static
    {
        $assistantResponse = $this->client->assistants()->create(array_merge_recursive([
            'model' => 'gpt-4-1106-preview',
            'name' => 'Test Assistant',
            'instructions' => 'You are an AI assistant. Please provide your feedback on the following prompt.',
            'tools' => [
                ['type' => 'retrieval'],
            ],
        ], $config));

        return new static($assistantResponse->id);
    }

    /**
     * Provide reading material to the assistant.
     * TODO: `$file` should be an array.
     */
    public function educate(string $file): static
    {
        $file = $this->client->files()->upload([
            'purpose' => 'assistants',
            'file' => fopen($file, 'rb'),
        ]);

        $this->client->assistants()->files()->create(
            $this->assistant->id,
            ['file_id' => $file->id]
        );

        return $this;
    }

    /**
     * Create a new thread.
     */
    public function createThread(array $parameters = []): static
    {
        $threadResponse = $this->client->threads()->create($parameters);

        $this->threadId = $threadResponse->id;

        return $this;
    }

    /**
     * Fetch all messages for the current thread.
     */
    public function messages(): OpenAI\Responses\Threads\Messages\ThreadMessageListResponse
    {
        // Fetch the messages from the run
        return $this->client->threads()->messages()->list($this->threadId);
    }

    /**
     * Write a new message.
     */
    public function write(string $message): static
    {
        $this->client->threads()->messages()->create(
            $this->threadId,
            ['role' => 'user', 'content' => $message],
        );

        return $this;
    }

    /**
     * Send all new messages to the assistant, and await a response.
     */
    public function send(): OpenAI\Responses\Threads\Messages\ThreadMessageListResponse
    {
        $threadRunResponse = $this->client->threads()->runs()->create(
            $this->threadId,
            ['assistant_id' => $this->assistant->id]
        );

        while ($this->working($threadRunResponse)) {
            // wait for the run to complete
            sleep(1);
        }

        return $this->messages();
    }

    protected function working(ThreadRunResponse $threadRunResponse): bool
    {
        sleep(1); // polling for the run status

        $threadRunResponse = $this->client->threads()->runs()->retrieve(
            threadId: $threadRunResponse->threadId,
            runId: $threadRunResponse->id
        );

        return $threadRunResponse->status !== 'completed';
    }
}
