<?php

namespace Urls;

use Torunar\OperationResult\OperationResult;

class Validator
{
    public const URL_MAX_LENGTH = 255;

    public function validate($urlData): OperationResult
    {
        $result = new OperationResult();

        $this->checkIsEmpty($urlData, $result);
        $this->checkStructure($urlData, $result);
        $this->checkLength($urlData, $result);

        return $result;
    }

    private function checkIsEmpty($urlData, OperationResult $result): void
    {
        if (!$result->isSuccessful() || !empty(trim($urlData['name']))) {
            return;
        }

        $result->addError('URL не должен быть пустым');
        $result->setIsSuccessful(false);
    }

    private function checkStructure($urlData, OperationResult $result): void
    {
        if (!$result->isSuccessful()) {
            return;
        }

        $parsedUrl = parse_url($urlData['name']);
        if (!empty($parsedUrl) && !empty($parsedUrl['scheme']) && !empty($parsedUrl['host'])) {
            $result->setData("{$parsedUrl['scheme']}://{$parsedUrl['host']}", 'url');
            return;
        }

        $result->addError('Некорректный URL');
        $result->setIsSuccessful(false);
    }

    private function checkLength($urlData, OperationResult $result): void
    {
        if (!$result->isSuccessful() || mb_strlen($urlData['name']) <= self::URL_MAX_LENGTH) {
            return;
        }

        $result->addError('Некорректный URL');
        $result->setIsSuccessful(false);
    }
}
