<?php

return [

    /*
    |-----------------------------------------------------------------
    | Default Prefix
    |-----------------------------------------------------------------
    |
    | This config option allows you to define a default prefix for
    | your icons. The dash separator will be applied automatically
    | to every icon name. It's required and needs to be unique.
    |
    */

    'prefix' => 'solar',

    /*
    |-----------------------------------------------------------------
    | Fallback Icon
    |-----------------------------------------------------------------
    |
    | This config option allows you to define a fallback
    | icon when an icon in this set cannot be found.
    |
    */

    'fallback' => '',

    /*
    |-----------------------------------------------------------------
    | Default Set Classes
    |-----------------------------------------------------------------
    |
    | This config option allows you to define some classes which
    | will be applied by default to all icons within this set.
    |
    */

    'class' => '',

    /*
    |-----------------------------------------------------------------
    | Default Set Attributes
    |-----------------------------------------------------------------
    |
    | This config option allows you to define some attributes which
    | will be applied by default to all icons within this set.
    |
    */

    'attributes' => [
        // Default stroke width for every icon. The generated files carry no
        // stroke-width (see config/generation.php), so it is applied exactly
        // once, and a passed value replaces it instead of duplicating the
        // attribute on the root <svg>.
        'stroke-width' => '1.5',
        // 'width' => 50,
        // 'height' => 50,
    ],

    /*
    |-----------------------------------------------------------------
    | Icon styles
    |-----------------------------------------------------------------
    |
    | Icons ship in six styles — linear, bold, broken, outline,
    | bold-duotone and line-duotone. The style is part of the
    | icon name: <x-solar-linear-heart />,
    | <x-solar-bold-heart />, ...
    |
    */

];
