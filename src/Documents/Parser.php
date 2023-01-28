<?php

namespace Documents;

use DiDom\Document;

class Parser
{
    /**
     * @var \DiDom\Document
     */
    private Document $document;

    /**
     * @param \DiDom\Document $document
     */
    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    /**
     * @return string[]
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function parse(): array
    {
        return [
            'h1'          => $this->getH1(),
            'title'       => $this->getTitle(),
            'description' => $this->getMetaContent(),
        ];
    }

    /**
     * @return string
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    private function getH1(): string
    {
        [$h1] = $this->document->find('h1');

        if (!$h1) {
            return '';
        }

        return strip_tags($h1->innerHtml());
    }

    /**
     * @return string
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    private function getTitle(): string
    {
        [$title] = $this->document->find('title');

        if (!$title) {
            return '';
        }

        return strip_tags($title->innerHtml());
    }

    /**
     * @return string
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    private function getMetaContent(): string
    {
        [$meta] = $this->document->find('meta[name=description]');

        if (!$meta) {
            return '';
        }

        $content = $meta->getAttribute('content');

        return $content ?: '';
    }
}
