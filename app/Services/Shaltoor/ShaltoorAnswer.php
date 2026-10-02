<?php

namespace App\Services\Shaltoor;

/**
 * One reply: plain text (the browser shows it as text, never HTML), the actions it offers (directions, call, WhatsApp,
 * a page), follow-up suggestions, the topic it was about and whether it answered from data.
 */
final readonly class ShaltoorAnswer
{
    /**
     * @param  list<array{kind: string, label: string, href: string}>  $actions
     * @param  list<string>  $suggestions
     */
    public function __construct(
        public string $text,
        public string $topic,
        public bool $answered,
        public array $actions = [],
        public array $suggestions = [],
    ) {}

    /** @return array{text: string, topic: string, answered: bool, actions: list<array{kind: string, label: string, href: string}>, suggestions: list<string>} */
    public function toArray(): array
    {
        return ['text' => $this->text, 'topic' => $this->topic, 'answered' => $this->answered, 'actions' => $this->actions, 'suggestions' => $this->suggestions];
    }
}
