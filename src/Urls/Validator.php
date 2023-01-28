<?php

namespace Urls;

use Torunar\OperationResult\OperationResult;

class Validator
{
    public const URL_MAX_LENGTH = 255;

    /**
     * @param array $urlData
     *
     * @return \Torunar\OperationResult\OperationResult
     */
    public function validate(array $urlData): OperationResult
    {
        $result = new OperationResult();

        $this->checkIsEmpty($urlData, $result);
        $this->checkStructure($urlData, $result);
        $this->checkLength($urlData, $result);

        return $result;
    }

    /**
     * @param array                                    $urlData
     * @param \Torunar\OperationResult\OperationResult $result
     *
     * @return void
     */
    private function checkIsEmpty(array $urlData, OperationResult $result): void
    {
        if (!$result->isSuccessful() || !empty(trim($urlData['name']))) {
            return;
        }

        $result->addError('URL не должен быть пустым');
        $result->setIsSuccessful(false);
    }

    /**
     * @param array                                    $urlData
     * @param \Torunar\OperationResult\OperationResult $result
     *
     * @return void
     */
    private function checkStructure(array $urlData, OperationResult $result): void
    {
        if (!$result->isSuccessful()) {
            return;
        }

        $parsedUrl = parse_url($urlData['name']);
        if (
            !empty($parsedUrl)
            && !empty($parsedUrl['scheme'])
            && $this->isCorrectScheme($parsedUrl['scheme'])
            && !empty($parsedUrl['host'])
        ) {
            $result->setData("{$parsedUrl['scheme']}://{$parsedUrl['host']}", 'url');
            return;
        }

        $result->addError('Некорректный URL');
        $result->setIsSuccessful(false);
    }

    /**
     * @param array                                    $urlData
     * @param \Torunar\OperationResult\OperationResult $result
     *
     * @return void
     */
    private function checkLength(array $urlData, OperationResult $result): void
    {
        if (!$result->isSuccessful() || mb_strlen($urlData['name']) <= self::URL_MAX_LENGTH) {
            return;
        }

        $result->addError('Некорректный URL');
        $result->setIsSuccessful(false);
    }

    /**
     * @param string $scheme
     *
     * @return bool
     */
    private function isCorrectScheme(string $scheme): bool
    {
        return in_array($scheme, ['http', 'https']);
    }
}
