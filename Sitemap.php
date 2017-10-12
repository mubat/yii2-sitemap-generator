<?php

namespace sergin\yii2\sitemap;


use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class Sitemap
 * @package sergin\yii2\sitemap
 *
 * @property SitemapElement[] $elements registered loc elements
 */
class Sitemap extends Model
{
    const CACHE_KEY = 'sitemap';
    const CACHE_LIFETIME = 86400; //seconds in a day

    /** @var array if this parameter set, alternate links will be populated */
    public $languages;

    /** @var SitemapElement[] */
    protected $_mapObjects = [];

    public function __get($name)
    {
        switch ($name) {
            case 'elements':
                return $this->_mapObjects;
        }
        return parent::__get($name);
    }

    /** @inheritdoc */
    public function canGetProperty($name, $checkVars = true, $checkBehaviors = true)
    {
        return $name === 'elements' || parent::canGetProperty($name, $checkVars, $checkBehaviors);
    }

    /**
     * @param $url          SitemapElement|array|string
     * @param array $config config for [[SitemapElement|SitemapElement]] object.
     *                      Can contain $loc, $lastmod, $changefreq, $priority keys
     * @return bool if sitemap object successful loaded
     */
    public function addUrl($url, $config = [])
    {
        if ($url instanceof SitemapElement) {
            $this->_mapObjects[] = $url;
            return true;
        }

        if (empty($config['loc'])) {
            $config['loc'] = $url;
        }

        $this->_mapObjects[] = new SitemapElement($config);
        return true;
    }

    public function validate($attributeNames = null, $clearErrors = true)
    {
        $isValid = true;
        foreach ($this->_mapObjects as $object) {
            $isValid = $isValid && $object->validate();
        }
        return $isValid;
    }

    /** @inheritDoc */
    public function load($data = null, $formName = null)
    {
        if ($data === null) $data = Yii::$app->cache->get(self::CACHE_KEY);

        if ($isLoaded = !empty($data)) {
            foreach ($data as $arrayElement) {
                $this->_mapObjects[] = new SitemapElement($arrayElement);
            }
        }
        return $isLoaded;
    }


    /**
     * Save $map to cache
     *
     * @return bool
     */
    public function save()
    {
        return Yii::$app->cache->set(self::CACHE_KEY, $this->generateArray(), 3600 * 24);
    }

    /** @return array */
    public function generateArray()
    {
        return ArrayHelper::toArray($this->_mapObjects);
    }

    /** @return string */
    public function generateXml()
    {
        $content = '<?xml version="1.0" encoding="UTF-8"?>';
        $content .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
        if (is_array($this->languages)) {
            $content .= ' xmlns:xhtml="http://www.w3.org/1999/xhtml"';
        }
        $content .= '>';

        foreach ($this->_mapObjects as $element) {
            if (!is_array($this->languages)) {
                $content .= $element->asXml();
                continue;
            }
            $alternateLinks = [];
            foreach ($this->languages as $language) {
                $alternateLinks[$language] = array_merge((array)$element->loc, ['language' => $language]);
            }
            $element->languages = $alternateLinks;
            foreach ($this->languages as $language) {
                $language !== Yii::$app->language &&
                $element->loc = array_merge((array)$element->loc, ['language' => $language]);

                $content .= $element->asXml();
            }
        }

        $content .= '</urlset>';
        return $content;
    }


}