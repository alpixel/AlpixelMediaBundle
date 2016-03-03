AlpixelMediaBundle
===================

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/a7a2bc25-0051-4a40-a820-e21972bb6272/mini.png)](https://insight.sensiolabs.com/projects/a7a2bc25-0051-4a40-a820-e21972bb6272)
[![Build Status](https://travis-ci.org/alpixel/AlpixelMediaBundle.svg?branch=master)](https://travis-ci.org/alpixel/AlpixelMediaBundle)
[![StyleCI](https://styleci.io/repos/50055872/shield)](https://styleci.io/repos/50055872)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/alpixel/AlpixelMediaBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/alpixel/AlpixelMediaBundle/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/alpixel/mediabundle/v/stable)](https://packagist.org/packages/alpixel/mediabundle)


The AlpixelMediaBundle is a bundle managing media of all kinds for our projects.

## Installation


* Install the package
```
composer require 'alpixel/mediabundle:~2.0'
```


* Update AppKernel.php
```

    <?php
    // app/AppKernel.php

    // ...
    class AppKernel extends Kernel
    {
        public function registerBundles()
        {
            $bundles = array(
                // ...

                new Alpixel\Bundle\MediaBundle\AlpixelMediaBundle(),
            );

            // ...
        }

        // ...
    }
```

* Update DB Schema

```
php app/console doctrine:schema:update
```

* Update your config.yml

```
twig:
    ...
    form:
        resources:
            - 'AlpixelMediaBundle:Form:fields.html.twig'


alpixel_media:
    upload_folder: "%kernel.root_dir%/../web/upload/"
    allowed_mimetypes: ['image/*', 'application/pdf']
    
liip_imagine:
    resolvers:
        default:
            web_path:
                web_root: %alpixel_media.upload_folder%
                cache_prefix: filters
    filter_sets:
        cache: ~
        admin:
            quality: 100
            filters:
                auto_rotate: ~
                thumbnail: { size: [140, 93], mode: outbound }
```


* Add the routing

```
alpixel_media:
    resource: '@AlpixelMediaBundle/Resources/config/routing.yml'
```

* Use it in front

There is a twig extension capable of generating URLs, you can call it this way :
```
<img src='{{myMedia|media_url}}' />
<img src='{{myMedia|media_url({public: false})}}' />
```

Available options are :
* public : [true]/false should the url be SEO friendly or not ?
* absolute : true/[false] should the URL be relative or not ?
* action : [show]/download what kind of action is expected.
* filter : the liip imagine filter used to render the image. Defaults to null (the original one)

You can also generate the URL by hand. Just look at the MediaController@showMediaAction to see what are the URL expected.
