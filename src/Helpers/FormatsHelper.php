<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\Helpers;

class FormatsHelper
{
    /**
     * Normalizes the format from config to the one accepted by Symfony HttpFoundation.
     *
     * @param array $configFormats
     *
     * @return array
     */
    public function normalizeFormats(array $configFormats): array
    {
        $formats = [];

        foreach ($configFormats as $format => $value) {
            foreach ((array) $value['mime_types'] as $mimeType) {
                $formats[$format][] = $mimeType;
            }
        }

        return $formats;
    }
}
