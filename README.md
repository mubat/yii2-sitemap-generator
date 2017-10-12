# yii2-sitemap-generator
Simple widget for [Yii2 framework](http://www.yiiframework.com) to generate sitemap. 
   
Support **XML**  format generation.

Support multi language. 

## Installation

### Composer

The preferred way to install this extension is through [Composer](http://getcomposer.org/).

Either run ```php composer require mubat/yii2-sitemap-generator```

or add ```"mubat/yii2-sitemap-generator" : "~1.0"``` to the require section of your ```composer.json```



Widget contains 2 models:
1. **Sitemap** - base model. It contains *SitemapElement* array and methods to generate XML.
    If `$languages` array published, model will generate *'alternate'* links.
2. **SitemapElement** - represents one `<url>` element. It contains:
   * `$loc` - **requrired** - page url (as array). Before insert into XML it will be process by `yii2\helpers\Url::toRoute()` method. 
   * `$updated_at` - *optional* - last page update;
   * `$changefreq` - **requrired**, default `'weekly'` - from class constant.
   * `$priority` - **requrired**, default `0.4` - page priority.
