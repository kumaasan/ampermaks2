<?php

namespace App\Services;

use App\Models\PostImage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use JsonException;

final class PostContentRenderer
{
    private const MAX_DEPTH = 20;

    private const MAX_IMAGES = 50;

    private const MAX_NODES = 5000;

    private const MAX_TEXT_LENGTH = 100000;

    /** @var array<string, PostImage> */
    private array $images = [];

    private int $nodeCount = 0;

    private int $textLength = 0;

    private bool $hasMeaningfulContent = false;

    /**
     * @param  array<string, mixed>  $document
     * @return array{json: array<string, mixed>, html: string, media_ids: list<string>}
     */
    public function render(array $document, int $userId): array
    {
        $this->reset();

        try {
            $encoded = json_encode($document, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $this->fail('Treść artykułu ma nieprawidłowy format.');
        }

        if (strlen($encoded) > 1_000_000) {
            $this->fail('Treść artykułu jest zbyt duża.');
        }

        $mediaIds = $this->collectMediaIds($document);

        if (count($mediaIds) > self::MAX_IMAGES) {
            $this->fail('Artykuł może zawierać maksymalnie 50 obrazów.');
        }

        if ($mediaIds !== []) {
            $images = PostImage::query()
                ->where('user_id', $userId)
                ->whereNull('post_id')
                ->whereIn('id', $mediaIds)
                ->get();

            foreach ($images as $image) {
                $this->images[$image->id] = $image;
            }

            if (count($this->images) !== count($mediaIds)) {
                $this->fail('Co najmniej jeden obraz nie istnieje, został już użyty albo nie należy do Ciebie.');
            }
        }

        $normalized = $this->normalizeNode($document, 0, true);

        if (! $this->hasMeaningfulContent) {
            $this->fail('Dodaj tekst lub obraz do artykułu.');
        }

        return [
            'json' => $normalized,
            'html' => $this->renderNode($normalized),
            'media_ids' => $mediaIds,
        ];
    }

    private function reset(): void
    {
        $this->images = [];
        $this->nodeCount = 0;
        $this->textLength = 0;
        $this->hasMeaningfulContent = false;
    }

    /**
     * @param  array<string, mixed>  $node
     * @return list<string>
     */
    private function collectMediaIds(array $node): array
    {
        $ids = [];

        if (($node['type'] ?? null) === 'image') {
            $attrs = is_array($node['attrs'] ?? null) ? $node['attrs'] : [];
            $id = $attrs['mediaId'] ?? null;

            if (! is_string($id) || ! Str::isUlid($id)) {
                $this->fail('Każdy obraz musi pochodzić z bezpiecznego uploadu.');
            }

            $ids[] = $id;
        }

        $content = $node['content'] ?? [];

        if (is_array($content)) {
            foreach ($content as $child) {
                if (is_array($child)) {
                    $ids = [...$ids, ...$this->collectMediaIds($child)];
                }
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * @param  array<string, mixed>  $node
     * @return array<string, mixed>
     */
    private function normalizeNode(array $node, int $depth, bool $isRoot = false): array
    {
        if ($depth > self::MAX_DEPTH) {
            $this->fail('Treść ma zbyt wiele poziomów zagnieżdżenia.');
        }

        if (++$this->nodeCount > self::MAX_NODES) {
            $this->fail('Treść zawiera zbyt wiele elementów.');
        }

        $type = $node['type'] ?? null;

        if (! is_string($type)) {
            $this->fail('Treść zawiera nieprawidłowy element.');
        }

        if ($isRoot && $type !== 'doc') {
            $this->fail('Treść artykułu nie jest dokumentem Tiptap.');
        }

        return match ($type) {
            'doc' => [
                'type' => 'doc',
                'content' => $this->normalizeChildren($node, $depth),
            ],
            'paragraph', 'blockquote', 'bulletList', 'listItem' => [
                'type' => $type,
                'content' => $this->normalizeChildren($node, $depth),
            ],
            'orderedList' => [
                'type' => 'orderedList',
                'attrs' => ['start' => $this->integerAttribute($node, 'start', 1, 1, 9999)],
                'content' => $this->normalizeChildren($node, $depth),
            ],
            'heading' => [
                'type' => 'heading',
                'attrs' => ['level' => $this->integerAttribute($node, 'level', 2, 2, 4)],
                'content' => $this->normalizeChildren($node, $depth),
            ],
            'codeBlock' => [
                'type' => 'codeBlock',
                'content' => $this->normalizeChildren($node, $depth),
            ],
            'horizontalRule', 'hardBreak' => ['type' => $type],
            'text' => $this->normalizeText($node),
            'image' => $this->normalizeImage($node),
            default => $this->fail("Niedozwolony element treści: {$type}."),
        };
    }

    /**
     * @param  array<string, mixed>  $node
     * @return list<array<string, mixed>>
     */
    private function normalizeChildren(array $node, int $depth): array
    {
        $content = $node['content'] ?? [];
        $parentType = is_string($node['type'] ?? null) ? $node['type'] : '';

        if (! is_array($content) || ! array_is_list($content)) {
            $this->fail('Treść zawiera nieprawidłową listę elementów.');
        }

        $normalized = [];

        foreach ($content as $child) {
            if (! is_array($child)) {
                $this->fail('Treść zawiera nieprawidłowy element.');
            }

            $normalizedChild = $this->normalizeNode($child, $depth + 1);

            if (! in_array($normalizedChild['type'], $this->allowedChildren($parentType), true)) {
                $this->fail("Element {$normalizedChild['type']} nie może znajdować się wewnątrz {$parentType}.");
            }

            $normalized[] = $normalizedChild;
        }

        return $normalized;
    }

    /** @return list<string> */
    private function allowedChildren(string $parentType): array
    {
        $blocks = [
            'paragraph',
            'heading',
            'blockquote',
            'bulletList',
            'orderedList',
            'codeBlock',
            'horizontalRule',
            'image',
        ];

        return match ($parentType) {
            'doc', 'blockquote' => $blocks,
            'paragraph', 'heading' => ['text', 'hardBreak'],
            'bulletList', 'orderedList' => ['listItem'],
            'listItem' => $blocks,
            'codeBlock' => ['text', 'hardBreak'],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $node
     * @return array<string, mixed>
     */
    private function normalizeText(array $node): array
    {
        $text = $node['text'] ?? null;

        if (! is_string($text)) {
            $this->fail('Treść zawiera nieprawidłowy tekst.');
        }

        $this->textLength += mb_strlen($text);

        if ($this->textLength > self::MAX_TEXT_LENGTH) {
            $this->fail('Tekst artykułu może mieć maksymalnie 100 000 znaków.');
        }

        if (trim($text) !== '') {
            $this->hasMeaningfulContent = true;
        }

        $normalized = [
            'type' => 'text',
            'text' => $text,
        ];

        $marks = $this->normalizeMarks($node['marks'] ?? []);

        if ($marks !== []) {
            $normalized['marks'] = $marks;
        }

        return $normalized;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function normalizeMarks(mixed $rawMarks): array
    {
        if (! is_array($rawMarks) || ! array_is_list($rawMarks)) {
            $this->fail('Tekst zawiera nieprawidłowe formatowanie.');
        }

        $marks = [];
        $seen = [];

        foreach ($rawMarks as $rawMark) {
            if (! is_array($rawMark) || ! is_string($rawMark['type'] ?? null)) {
                $this->fail('Tekst zawiera nieprawidłowe formatowanie.');
            }

            $type = $rawMark['type'];

            if (isset($seen[$type])) {
                continue;
            }

            $seen[$type] = true;

            if (in_array($type, ['bold', 'italic', 'underline', 'strike', 'code'], true)) {
                $marks[] = ['type' => $type];

                continue;
            }

            if ($type === 'link') {
                $attrs = is_array($rawMark['attrs'] ?? null) ? $rawMark['attrs'] : [];
                $href = $attrs['href'] ?? null;

                if (! is_string($href)) {
                    $this->fail('Link nie ma prawidłowego adresu.');
                }

                $marks[] = [
                    'type' => 'link',
                    'attrs' => ['href' => $this->normalizeHref($href)],
                ];

                continue;
            }

            $this->fail("Niedozwolone formatowanie tekstu: {$type}.");
        }

        return $marks;
    }

    /**
     * @param  array<string, mixed>  $node
     * @return array<string, mixed>
     */
    private function normalizeImage(array $node): array
    {
        $attrs = is_array($node['attrs'] ?? null) ? $node['attrs'] : [];
        $mediaId = $attrs['mediaId'] ?? null;

        if (! is_string($mediaId) || ! isset($this->images[$mediaId])) {
            $this->fail('Obraz nie pochodzi z bezpiecznego uploadu.');
        }

        $alt = trim(strip_tags(is_string($attrs['alt'] ?? null) ? $attrs['alt'] : ''));

        if ($alt === '') {
            $this->fail('Uzupełnij tekst alternatywny każdego obrazu.');
        }

        if (mb_strlen($alt) > 250) {
            $this->fail('Tekst alternatywny obrazu może mieć maksymalnie 250 znaków.');
        }

        $image = $this->images[$mediaId];
        $requestedWidth = filter_var($attrs['width'] ?? null, FILTER_VALIDATE_INT);
        $maximumWidth = min($image->width, 1600);
        $width = $requestedWidth === false
            ? min($image->width, 1200)
            : max(120, min($requestedWidth, $maximumWidth));
        $height = max(1, (int) round($width * ($image->height / $image->width)));
        $align = is_string($attrs['align'] ?? null) ? $attrs['align'] : 'center';

        if (! in_array($align, ['left', 'center', 'right'], true)) {
            $align = 'center';
        }

        $this->hasMeaningfulContent = true;

        return [
            'type' => 'image',
            'attrs' => [
                'src' => $image->url(),
                'alt' => $alt,
                'title' => null,
                'width' => $width,
                'height' => $height,
                'mediaId' => $mediaId,
                'align' => $align,
            ],
        ];
    }

    /** @param array<string, mixed> $node */
    private function integerAttribute(array $node, string $name, int $default, int $min, int $max): int
    {
        $attrs = is_array($node['attrs'] ?? null) ? $node['attrs'] : [];
        $value = filter_var($attrs[$name] ?? null, FILTER_VALIDATE_INT);

        return $value === false ? $default : max($min, min($value, $max));
    }

    private function normalizeHref(string $href): string
    {
        $href = trim($href);

        if ($href === '' || strlen($href) > 2048 || preg_match('/[\x00-\x1F\x7F]/', $href) === 1) {
            $this->fail('Link ma nieprawidłowy adres.');
        }

        if ((str_starts_with($href, '/') && ! str_starts_with($href, '//')) || str_starts_with($href, '#')) {
            return $href;
        }

        $scheme = strtolower((string) parse_url($href, PHP_URL_SCHEME));

        if (! in_array($scheme, ['http', 'https', 'mailto'], true)) {
            $this->fail('Link może używać tylko protokołu HTTP, HTTPS lub mailto.');
        }

        if (in_array($scheme, ['http', 'https'], true) && parse_url($href, PHP_URL_HOST) === null) {
            $this->fail('Link ma nieprawidłowy adres.');
        }

        return $href;
    }

    /** @param array<string, mixed> $node */
    private function renderNode(array $node): string
    {
        $type = $node['type'];

        return match ($type) {
            'doc' => $this->renderChildren($node['content']),
            'paragraph' => '<p>'.$this->renderChildren($node['content']).'</p>',
            'heading' => '<h'.$node['attrs']['level'].'>'.$this->renderChildren($node['content']).'</h'.$node['attrs']['level'].'>',
            'blockquote' => '<blockquote>'.$this->renderChildren($node['content']).'</blockquote>',
            'bulletList' => '<ul>'.$this->renderChildren($node['content']).'</ul>',
            'orderedList' => '<ol start="'.$node['attrs']['start'].'">'.$this->renderChildren($node['content']).'</ol>',
            'listItem' => '<li>'.$this->renderChildren($node['content']).'</li>',
            'codeBlock' => '<pre><code>'.$this->escape($this->plainText($node['content'])).'</code></pre>',
            'horizontalRule' => '<hr>',
            'hardBreak' => '<br>',
            'text' => $this->renderText($node),
            'image' => $this->renderImage($node),
            default => '',
        };
    }

    /** @param list<array<string, mixed>> $nodes */
    private function renderChildren(array $nodes): string
    {
        return implode('', array_map(fn (array $node): string => $this->renderNode($node), $nodes));
    }

    /** @param list<array<string, mixed>> $nodes */
    private function plainText(array $nodes): string
    {
        $text = '';

        foreach ($nodes as $node) {
            $text .= match ($node['type']) {
                'text' => $node['text'],
                'hardBreak' => "\n",
                default => isset($node['content']) ? $this->plainText($node['content']) : '',
            };
        }

        return $text;
    }

    /** @param array<string, mixed> $node */
    private function renderText(array $node): string
    {
        $html = $this->escape($node['text']);

        foreach ($node['marks'] ?? [] as $mark) {
            $html = match ($mark['type']) {
                'bold' => '<strong>'.$html.'</strong>',
                'italic' => '<em>'.$html.'</em>',
                'underline' => '<u>'.$html.'</u>',
                'strike' => '<s>'.$html.'</s>',
                'code' => '<code>'.$html.'</code>',
                'link' => '<a href="'.$this->escape($mark['attrs']['href']).'" target="_blank" rel="noopener noreferrer nofollow">'.$html.'</a>',
                default => $html,
            };
        }

        return $html;
    }

    /** @param array<string, mixed> $node */
    private function renderImage(array $node): string
    {
        $attrs = $node['attrs'];

        return sprintf(
            '<figure class="blog-image" data-align="%s"><img src="%s" alt="%s" width="%d" height="%d" loading="lazy" decoding="async"></figure>',
            $this->escape($attrs['align']),
            $this->escape($attrs['src']),
            $this->escape($attrs['alt']),
            $attrs['width'],
            $attrs['height'],
        );
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['content' => $message]);
    }
}
