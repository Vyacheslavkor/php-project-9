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
        $elms = $this->document->find('h1');
        if (empty($elms)) {
            return '';
        }

        [$h1] = $elms;

        return method_exists($h1, 'innerHtml') ? strip_tags($h1->innerHtml()) : '';
    }

    /**
     * @return string
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    private function getTitle(): string
    {
        $elms = $this->document->find('title');
        if (empty($elms)) {
            return '';
        }

        [$title] = $elms;

        return method_exists($title, 'innerHtml') ? strip_tags($title->innerHtml()) : '';
    }

    /**
     * @return string
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    private function getMetaContent(): string
    {
        $elms = $this->document->find('meta[name=description]');

        if (empty($elms)) {
            return '';
        }

        [$meta] = $elms;
        $content = $meta->getAttribute('content');

        return $content ?: '';
    }
}
