<?php

declare(strict_types=1);

namespace Retrofit\Drupal\Render;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Render\AttachmentsInterface;
use Drupal\Core\Render\AttachmentsResponseProcessorInterface;

final class RetrofitHtmlResponseAttachmentsProcessor implements AttachmentsResponseProcessorInterface
{
    public function __construct(
        private readonly AttachmentsResponseProcessorInterface $inner,
    ) {
    }

    public function processAttachments(AttachmentsInterface $response)
    {
        $attachments = $response->getAttachments();
        if (isset($attachments['js']) && is_array($attachments['js'])) {
            foreach ($attachments['js'] as $key => $item) {
                if (isset($item['type'], $item['data']) && $item['type'] === 'setting') {
                    $attachments['drupalSettings'] = NestedArray::mergeDeepArray(
                        [$attachments['drupalSettings'] ?? [], $item['data']],
                        true,
                    );
                    unset($attachments['js'][$key]);
                }
            }
        }
        // @todo log these removals?
        unset($attachments['css'], $attachments['js']);
        $response->setAttachments($attachments);
        return $this->inner->processAttachments($response);
    }
}
