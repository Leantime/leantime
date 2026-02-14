@php
    /** @var array $activity */
    /** @var int $articleId */

    $actionIcons = [
        'article.create' => 'fa fa-plus',
        'article.edit' => 'fa fa-edit',
        'article.title' => 'fa fa-heading',
        'article.status' => 'fa fa-circle-dot',
        'article.parent' => 'fa fa-folder-tree',
        'article.milestone' => 'fa fa-flag',
        'article.tags' => 'fa fa-tags',
        'article.icon' => 'fa fa-icons',
    ];

    $actionClasses = [
        'article.create' => '',
        'article.edit' => 'edit',
        'article.title' => 'edit',
        'article.status' => 'status',
        'article.parent' => '',
        'article.milestone' => '',
        'article.tags' => 'edit',
        'article.icon' => '',
    ];

    /**
     * Build a natural-language label based on the action and its values.
     */
    function getActivityLabel(string $action, array $values): string
    {
        switch ($action) {
            case 'article.create':
                return 'created the article';

            case 'article.edit':
                return 'edited document text';

            case 'article.title':
                $to = $values['to'] ?? '';
                return $to !== '' ? 'renamed the article to "' . e($to) . '"' : 'renamed the article';

            case 'article.status':
                $to = $values['to'] ?? '';
                if ($to === 'published') {
                    return 'published the article';
                } elseif ($to === 'draft') {
                    return 'reverted to draft';
                }
                return 'changed the status';

            case 'article.parent':
                $to = $values['to'] ?? '';
                if (empty($to) || $to === '0') {
                    return 'removed the parent article';
                }
                return 'added a parent article';

            case 'article.milestone':
                $to = $values['to'] ?? '';
                if (empty($to) || $to === '0') {
                    return 'removed the milestone';
                }
                return 'added a milestone';

            case 'article.tags':
                return 'updated tags';

            case 'article.icon':
                return 'changed the icon';

            default:
                return 'updated the article';
        }
    }
@endphp

<div class="wiki-activity-feed" id="wikiActivityFeed">
    @forelse ($activity as $item)
        @php
            $action = $item['action'] ?? '';
            $icon = $actionIcons[$action] ?? 'fa fa-circle';
            $cssClass = $actionClasses[$action] ?? '';
            $name = trim(($item['firstname'] ?? '') . ' ' . ($item['lastname'] ?? ''));
            $date = $item['date'] ?? '';
            $values = $item['values'] ?? [];
            $label = getActivityLabel($action, $values);
        @endphp
        <div class="wiki-activity-item">
            <div class="wiki-activity-icon {{ $cssClass }}">
                <i class="{{ $icon }}"></i>
            </div>
            <div class="wiki-activity-content">
                <div class="wiki-activity-text">
                    <strong>{{ $name ?: 'Someone' }}</strong> {{ $label }}
                </div>
                @if (!empty($date))
                    <div class="wiki-activity-time">{{ format($date)->date() }}</div>
                @endif
            </div>
        </div>
    @empty
        <div class="wiki-activity-empty">
            <span>No activity yet</span>
        </div>
    @endforelse
</div>
