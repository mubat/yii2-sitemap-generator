<?php

namespace sergin\yii2\sitemap;


use yii\base\Model;
use yii\helpers\Json;
use yii\helpers\StringHelper;
use yii\helpers\Url;

/**
 * Class SitemapElement
 * @package sergin\yii2\sitemap
 */
class SitemapElement extends Model
{
    public $loc;
    public $updated_at;
    /** @var  string one of the freeqonce. user model constants. Default 'weekly' */
    public $changefreq;
    /** @var float location priority. Default 0.4 */
    public $priority = 0.4;

    /** @var  array alternate links to several languages. Each array element should declared as ['langCode' => 'url'] */
    public $languages;

    const CHANGE_FREQ_ALWAYS = 'always';
    const CHANGE_FREQ_HOURLY = 'hourly';
    const CHANGE_FREQ_DAILY = 'daily';
    const CHANGE_FREQ_WEEKLY = 'weekly';
    const CHANGE_FREQ_MONTHLY = 'monthly';
    const CHANGE_FREQ_YEARLY = 'yearly';
    const CHANGE_FREQ_NEVER = 'never';

    /** @inheritdoc */
    public function rules()
    {
        return [
            ['loc', 'required'],
            ['loc', 'url'],
            ['updated_at', 'default', 'value' => null],

            ['priority', 'double'],

            ['changefreq', 'default', 'value' => self::CHANGE_FREQ_WEEKLY],
            ['changefreq', 'in', 'range' => [
                self::CHANGE_FREQ_ALWAYS,
                self::CHANGE_FREQ_HOURLY,
                self::CHANGE_FREQ_DAILY,
                self::CHANGE_FREQ_WEEKLY,
                self::CHANGE_FREQ_MONTHLY,
                self::CHANGE_FREQ_YEARLY,
                self::CHANGE_FREQ_NEVER,
            ]],
        ];
    }

    public function asJson()
    {
        return Json::encode($this->toArray(['loc', 'updated_at', 'changefreq', 'priority']));
    }

    public function asXml()
    {

        if (is_array($loc = $this->loc) || !StringHelper::startsWith($loc, 'http')) {
            $loc = Url::to($loc, true);
        }
        $content = "<url><loc>$loc</loc>";

        if (!empty($this->languages)) {
            foreach ($this->languages as $code => $langUrl) {
                $langUrl = (array)$langUrl;
                $langUrl['language'] = $code;

                $content .= '<xhtml:link rel="alternate" hreflang="' . $code
                    . '" href="' . Url::to($langUrl, true) . '"/>';
            }
        }

        if (!empty($this->updated_at)) {
            $content .= "<changefreq>$this->updated_at</changefreq>";
        }

        $content .= "<priority>$this->priority</priority></url>";

        return $content;
    }
}