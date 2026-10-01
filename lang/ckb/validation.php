<?php

/*
|--------------------------------------------------------------------------
| Validation Language Lines
|--------------------------------------------------------------------------
|
| The rules used by this application. Any rule that is missing here falls
| back to the English message in lang/en/validation.php.
|
*/

return [

    'accepted' => 'دەبێت :attribute پەسەند بکرێت.',
    'after' => 'دەبێت :attribute بەروارێک بێت دوای :date.',
    'alpha' => ':attribute دەبێت تەنها پیت لەخۆبگرێت.',
    'alpha_dash' => ':attribute دەبێت تەنها پیت، ژمارە، هێڵی ناوەڕاست و ژێرهێڵ لەخۆبگرێت.',
    'alpha_num' => ':attribute دەبێت تەنها پیت و ژمارە لەخۆبگرێت.',
    'array' => 'دەبێت :attribute لیستێک بێت.',
    'before' => 'دەبێت :attribute بەروارێک بێت پێش :date.',
    'between' => [
        'array' => 'دەبێت :attribute لە نێوان :min و :max دانەدا بێت.',
        'file' => 'دەبێت قەبارەی :attribute لە نێوان :min و :max کیلۆبایتدا بێت.',
        'numeric' => 'دەبێت :attribute لە نێوان :min و :max دا بێت.',
        'string' => 'دەبێت درێژیی :attribute لە نێوان :min و :max پیتدا بێت.',
    ],
    'boolean' => 'دەبێت :attribute ڕاست یان هەڵە بێت.',
    'confirmed' => 'دووپاتکردنەوەی :attribute یەک ناگرێتەوە.',
    'current_password' => 'وشەی نهێنی هەڵەیە.',
    'date' => ':attribute بەروارێکی دروست نییە.',
    'different' => 'دەبێت :attribute و :other جیاواز بن.',
    'digits' => 'دەبێت :attribute :digits ژمارە بێت.',
    'email' => 'دەبێت :attribute ناونیشانێکی ئیمەیڵی دروست بێت.',
    'exists' => ':attribute ـی هەڵبژێردراو دروست نییە.',
    'file' => 'دەبێت :attribute فایل بێت.',
    'filled' => 'دەبێت :attribute بەهایەکی هەبێت.',
    'image' => 'دەبێت :attribute وێنە بێت.',
    'in' => ':attribute ـی هەڵبژێردراو دروست نییە.',
    'integer' => 'دەبێت :attribute ژمارەیەکی تەواو بێت.',
    'lowercase' => 'دەبێت :attribute بە پیتی بچووک بێت.',
    'max' => [
        'array' => ':attribute نابێت لە :max دانە زیاتر بێت.',
        'file' => 'قەبارەی :attribute نابێت لە :max کیلۆبایت زیاتر بێت.',
        'numeric' => ':attribute نابێت لە :max گەورەتر بێت.',
        'string' => ':attribute نابێت لە :max پیت درێژتر بێت.',
    ],
    'mimes' => 'دەبێت :attribute فایلێک بێت لە جۆری: :values.',
    'min' => [
        'array' => 'دەبێت :attribute لانیکەم :min دانەی هەبێت.',
        'file' => 'دەبێت قەبارەی :attribute لانیکەم :min کیلۆبایت بێت.',
        'numeric' => 'دەبێت :attribute لانیکەم :min بێت.',
        'string' => 'دەبێت :attribute لانیکەم :min پیت بێت.',
    ],
    'not_in' => ':attribute ـی هەڵبژێردراو دروست نییە.',
    'numeric' => 'دەبێت :attribute ژمارە بێت.',
    'password' => [
        'letters' => 'دەبێت :attribute لانیکەم یەک پیتی تێدابێت.',
        'mixed' => 'دەبێت :attribute لانیکەم یەک پیتی گەورە و یەک پیتی بچووکی تێدابێت.',
        'numbers' => 'دەبێت :attribute لانیکەم یەک ژمارەی تێدابێت.',
        'symbols' => 'دەبێت :attribute لانیکەم یەک هێمای تێدابێت.',
        'uncompromised' => 'ئەم :attribute ـە لە دزەپێکردنی زانیارییەکدا دەرکەوتووە. تکایە :attribute ـێکی تر هەڵبژێرە.',
    ],
    'regex' => 'شێوازی :attribute دروست نییە.',
    'required' => 'خانەی :attribute پێویستە.',
    'required_if' => 'خانەی :attribute پێویستە کاتێک :other بریتییە لە :value.',
    'required_with' => 'خانەی :attribute پێویستە کاتێک :values هەبێت.',
    'same' => 'دەبێت :attribute و :other وەک یەک بن.',
    'size' => [
        'array' => 'دەبێت :attribute :size دانەی هەبێت.',
        'file' => 'دەبێت قەبارەی :attribute :size کیلۆبایت بێت.',
        'numeric' => 'دەبێت :attribute :size بێت.',
        'string' => 'دەبێت :attribute :size پیت بێت.',
    ],
    'string' => 'دەبێت :attribute دەق بێت.',
    'unique' => 'ئەم :attribute ـە پێشتر بەکارهاتووە.',
    'uploaded' => 'بارکردنی :attribute سەرنەکەوت.',
    'url' => 'دەبێت :attribute بەستەرێکی دروست بێت.',
    'uuid' => 'دەبێت :attribute UUID ـێکی دروست بێت.',

    'attributes' => [
        'name' => 'ناو',
        'email' => 'ئیمەیڵ',
        'password' => 'وشەی نهێنی',
        'current_password' => 'وشەی نهێنیی ئێستا',
        'code' => 'کۆد',
        'recovery_code' => 'کۆدی گەڕاندنەوە',
        'locale' => 'زمان',
        'form.name' => 'ناو',
        'form.email' => 'ئیمەیڵ',
        'form.password' => 'وشەی نهێنی',
        'form.roles' => 'ڕۆڵەکان',
        'form.roles.*' => 'ڕۆڵ',
        'form.locale' => 'زمان',
        'form.permissions' => 'مۆڵەتەکان',
        'form.permissions.*' => 'مۆڵەت',
    ],

];
