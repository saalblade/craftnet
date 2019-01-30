<?php

namespace craftnet\controllers\api\v1;

use Craft;
use craft\helpers\StringHelper;
use craft\i18n\Locale;
use craft\web\UploadedFile;
use craftnet\cms\CmsLicense;
use craftnet\controllers\api\BaseApiController;
use GuzzleHttp\RequestOptions;
use yii\web\Response;

/**
 * Class SupportController
 */
class SupportController extends BaseApiController
{
    /**
     * Creates a new support request
     *
     * @return Response
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionCreate(): Response
    {
        $client = Craft::createGuzzleClient([
            'base_uri' => 'https://api2.frontapp.com',
        ]);

        $headers = [
            'Authorization' => 'Bearer ' . getenv('FRONT_TOKEN'),
            'Accept' => 'application/json',
        ];

        $request = Craft::$app->getRequest();
        $requestHeaders = $request->getHeaders();
        $body = $request->getRequiredBodyParam('message');

        $info = [];
        /** @var CmsLicense $cmsLicense */
        $cmsLicense = reset($this->cmsLicenses) ?: null;
        $formatter = Craft::$app->getFormatter();

        if ($this->cmsEdition !== null || $this->cmsVersion !== null) {
            $craftInfo = 'Craft' .
                ($this->cmsEdition !== null ? ' ' . ucfirst($this->cmsEdition) : '') .
                ($this->cmsVersion !== null ? ' ' . $this->cmsVersion : '');

            if ($cmsLicense && $cmsLicense->editionHandle !== $this->cmsEdition) {
                $craftInfo .= ' (trial)';
            }

            $info[] = $craftInfo;
        }

        if ($cmsLicense) {
            $licenseInfo = [
                '`' . $cmsLicense->getShortKey() . '` (' . ucfirst($cmsLicense->editionHandle) . ')',
                'from ' . $formatter->asDate($cmsLicense->dateCreated, Locale::LENGTH_SHORT),
            ];
            if ($cmsLicense->expirable) {
                $licenseInfo[] .= ($cmsLicense->expired ? 'expired on' : 'expires on') .
                    ' '. $formatter->asDate($cmsLicense->expiresOn, Locale::LENGTH_SHORT);
            }
            if ($cmsLicense->domain) {
                $licenseInfo[] = 'for ' . $cmsLicense->domain;
            }
            $info[] = 'License: ' . implode(', ', $licenseInfo);
        }

        if (!empty($this->pluginVersions)) {
            $pluginInfos = [];
            foreach ($this->pluginVersions as $pluginHandle => $pluginVersion) {
                if ($plugin = $this->plugins[$pluginHandle] ?? null) {
                    $pluginInfo = "[{$plugin->name}](https://plugins.craftcms.com/{$plugin->handle})";
                } else {
                    $pluginInfo = $$pluginHandle;
                }
                if (($edition = $this->pluginEditions[$pluginHandle] ?? null) && $edition !== 'standard') {
                    $pluginInfo .= ' ' . ucfirst($edition);
                }
                $pluginInfo .= ' ' . $pluginVersion;
                $pluginInfos[] = $pluginInfo;
            }
            $info[] = 'Plugins: ' . implode(', ', $pluginInfos);
        }

        if (($host = $requestHeaders->get('X-Craft-Host')) !== null) {
            $info[] = 'Host: ' . $host;
        }

        if (!empty($info)) {
            $body .= "\n\n---\n\n" . implode("  \n", $info);
        }

        $parts = [
            [
                'name' => 'sender[handle]',
                'contents' => $request->getRequiredBodyParam('email'),
            ],
            [
                'name' => 'sender[name]',
                'contents' => $request->getRequiredBodyParam('name'),
            ],
            [
                'name' => 'to[]',
                'contents' => getenv('FRONT_TO_EMAIL'),
            ],
            [
                'name' => 'subject',
                'contents' => getenv('FRONT_SUBJECT'),
            ],
            [
                'name' => 'body',
                'contents' => $body,
            ],
            [
                'name' => 'body_format',
                'contents' => 'markdown',
            ],
            [
                'name' => 'external_id',
                'contents' => StringHelper::UUID(),
            ],
            [
                'name' => 'created_at',
                'contents' => time(),
            ],
            [
                'name' => 'type',
                'contents' => 'email',
            ],
            [
                'name' => 'tags[]',
                'contents' => getenv('FRONT_TAG'),
            ],
            [
                'name' => 'metadata[thread_ref]',
                'contents' => StringHelper::UUID(),
            ],
            [
                'name' => 'metadata[is_inbound]',
                'contents' => 'true',
            ],
            [
                'name' => 'metadata[is_archived]',
                'contents' => 'false',
            ],
            [
                'name' => 'metadata[should_skip_rules]',
                'contents' => getenv('FRONT_SKIP_RULES') ?: 'true',
            ],
        ];

        $attachments = UploadedFile::getInstancesByName('attachments');
        if (empty($attachments) && $attachment = UploadedFile::getInstanceByName('attachment')) {
            $attachments = [$attachment];
        }

        foreach ($attachments as $i => $attachment) {
            $parts[] = [
                'name' => "attachments[{$i}]",
                'contents' => fopen($attachment->tempName, 'rb'),
                'filename' => $attachment->name,
            ];
        }

        $client->post('/inboxes/' . getenv('FRONT_INBOX_ID') . '/imported_messages', [
            RequestOptions::HEADERS => $headers,
            RequestOptions::MULTIPART => $parts,
        ]);

        return $this->asJson([
            'sent' => true,
        ]);
    }
}
