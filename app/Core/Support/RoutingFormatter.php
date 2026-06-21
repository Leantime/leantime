<?php

namespace Leantime\Core\Support;

/**
 * Routing Formatter - Formats conversation history and routing context for AI consumption.
 */
class RoutingFormatter extends AbstractEntityFormatter
{
    protected array $conversationHistory;

    public function __construct(array $conversationHistory = [])
    {
        $this->conversationHistory = $conversationHistory;

        // Set field priority for conversation history formatting
        $this->fieldPriority = $this->getPriorityFields();

    }

    /**
     * Prepare the entity data for formatting.
     */
    protected function prepareEntityData(array $context = []): array
    {
        return [
            'conversation_history' => $this->conversationHistory,
            'exchange_count' => count($this->conversationHistory),
            'latest_date' => $this->getLatestDate(),
            'has_feedback' => $this->hasFeedback(),
        ];
    }

    /**
     * Format the header section of the entity.
     */
    protected function formatHeader(array $data): string
    {
        $count = $data['exchange_count'] ?? 0;
        $latestDate = $data['latest_date'] ?? 'Unknown';

        if ($count === 0) {
            return "## Recent Conversation History\n\nNo recent conversation history available.";
        }

        return "## Recent Conversation History\n\n".
               "**Total Exchanges:** {$count}\n".
               "**Latest Exchange:** {$latestDate}\n\n".
               'The following shows recent interactions between the user and assistant:';
    }

    /**
     * Format the body section with conversation history.
     */
    protected function formatBody(array $data, array $context = []): string
    {
        if (empty($data['conversation_history'])) {
            return '';
        }

        return $this->formatRecentHistoryAsPrompt($data['conversation_history']);
    }

    /**
     * Format a compact summary of the conversation history.
     */
    protected function formatSummary(array $data): string
    {
        $count = $data['exchange_count'] ?? 0;

        if ($count === 0) {
            return 'No conversation history';
        }

        $latestDate = $data['latest_date'] ?? 'Unknown date';

        return "Conversation history with {$count} exchanges, latest from {$latestDate}";
    }

    /**
     * Get entity type.
     */
    public function getEntityType(): string
    {
        return 'routing';
    }

    /**
     * Get entity identifier.
     */
    public function getEntityId(): string
    {
        return 'conversation_history_'.count($this->conversationHistory);
    }

    /**
     * Get the date of the latest conversation exchange.
     */
    protected function getLatestDate(): string
    {
        if (empty($this->conversationHistory)) {
            return 'No exchanges';
        }

        $latest = end($this->conversationHistory);

        return $latest['date'] ?? 'Unknown date';
    }

    /**
     * Check if any conversation exchange has feedback.
     */
    protected function hasFeedback(): bool
    {
        foreach ($this->conversationHistory as $exchange) {
            if (isset($exchange['feedback']) && $exchange['feedback'] !== null) {
                return true;
            }
        }

        return false;
    }

    /**
     * Format recent conversation history as a prompt.
     *
     * @param  array  $recentHistory  An array containing the recent conversation history. Each item should include
     *                                keys for 'date', 'message', 'response', and 'feedback'.
     * @return string The formatted conversation history as a string in prompt format.
     */
    protected function formatRecentHistoryAsPrompt(array $recentHistory): string
    {
        $prompt = '';

        // Add recent conversation history if available
        if (! empty($recentHistory)) {
            foreach ($recentHistory as $exchange) {
                $date = $this->sanitizeValue($exchange['date'] ?? 'Unknown date');
                $message = $this->sanitizeValue($exchange['message'] ?? '');
                $response = $this->sanitizeValue($exchange['response'] ?? '');

                $prompt .= "**Date (UTC):** {$date}\n";
                $prompt .= "**User:** {$message}\n";
                $prompt .= "**Assistant:** {$response}\n\n";

                $feedback = $this->formatFeedback($exchange['feedback'] ?? null);
                $prompt .= "**User Rating:** {$feedback}\n\n";
                $prompt .= "---\n\n";
            }
        }

        return $prompt;
    }

    /**
     * Format feedback value into human-readable text.
     */
    protected function formatFeedback(mixed $feedback): string
    {
        switch ($feedback) {
            case 1:
                return 'Positive';
            case -1:
                return 'Negative';
            default:
                return 'Neutral (no feedback)';
        }
    }

    /**
     * Get priority fields for this formatter (conversation history specific).
     */
    protected function getPriorityFields(): array
    {
        return [
            'date',
            'message',
            'response',
            'feedback',
            'id',
        ];
    }

    /**
     * Format optimized conversation history for token efficiency.
     *
     * @param  array  $optimizedHistory  History with full_exchanges and key_facts_exchanges
     * @return array Formatted history ready for AI consumption
     */
    public function formatOptimizedHistory(array $optimizedHistory): array
    {
        $formattedHistory = [];

        // Add recent full exchanges first
        if (! empty($optimizedHistory['full_exchanges'])) {
            $formattedHistory = array_merge($formattedHistory, $optimizedHistory['full_exchanges']);
        }

        // Add key facts exchanges as compressed entries
        if (! empty($optimizedHistory['key_facts_exchanges'])) {
            foreach ($optimizedHistory['key_facts_exchanges'] as $keyFactExchange) {
                if (! empty($keyFactExchange['key_facts'])) {
                    $formattedHistory[] = [
                        'id' => $keyFactExchange['id'],
                        'date' => $keyFactExchange['date'],
                        'message' => '[Key Facts]',
                        'response' => $keyFactExchange['key_facts'],
                        'feedback' => $keyFactExchange['feedback'],
                    ];
                }
            }
        }

        return $formattedHistory;
    }

    /**
     * Create a routing formatter from optimized history data.
     */
    public static function fromOptimizedHistory(array $optimizedHistory): self
    {
        $formatter = new self;
        $formattedHistory = $formatter->formatOptimizedHistory($optimizedHistory);
        $formatter->conversationHistory = $formattedHistory;

        return $formatter;
    }

    /**
     * Update the conversation history.
     */
    public function setConversationHistory(array $conversationHistory): self
    {
        $this->conversationHistory = $conversationHistory;

        return $this;
    }

    /**
     * Get the current conversation history.
     */
    public function getConversationHistory(): array
    {
        return $this->conversationHistory;
    }
}
