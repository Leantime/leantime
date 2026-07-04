<?php

namespace Leantime\Core\Support;

/**
 * Interface for entity formatters that convert domain models into markdown for AI consumption.
 *
 * This interface defines the contract for formatting domain entities (tickets, projects, users, etc.)
 * into structured markdown documents suitable for AI prompts and embeddings.
 */
interface EntityFormatterInterface
{
    /**
     * Format the entity into a standard markdown representation.
     *
     * @return string Markdown formatted string representing the entity
     */
    public function format(): string;

    /**
     * Format the entity with additional context-aware information.
     *
     * @param  array  $context  Additional context that might affect formatting (e.g., user preferences, specific fields to include/exclude)
     * @return string Context-aware markdown formatted string
     */
    public function formatForContext(array $context = []): string;

    /**
     * Get the type of entity this formatter handles.
     *
     * @return string Entity type (e.g., 'ticket', 'project', 'user')
     */
    public function getEntityType(): string;

    /**
     * Get the unique identifier of the entity being formatted.
     *
     * @return mixed Entity ID (could be int, string, or other identifier type)
     */
    public function getEntityId(): mixed;

    /**
     * Get a compact, one-line summary of the entity.
     *
     * @return string Brief summary suitable for lists or references
     */
    public function getSummary(): string;
}
