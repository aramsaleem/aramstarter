<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Supported Locales
    |--------------------------------------------------------------------------
    |
    | The languages users can pick from the language switcher. The key is the
    | locale code used for the translation files in /lang (e.g. lang/ar.json),
    | "native" is the label shown to users and "dir" sets the text direction.
    |
    | To add a language: add an entry here, then copy lang/ar.json and the
    | lang/ar directory to the new locale code and translate the strings.
    |
    */

    'supported' => [
        'en' => ['name' => 'English', 'native' => 'English', 'dir' => 'ltr'],
        'ar' => ['name' => 'Arabic', 'native' => 'العربية', 'dir' => 'rtl'],
        'ckb' => ['name' => 'Central Kurdish', 'native' => 'کوردی', 'dir' => 'rtl'],
    ],

];
