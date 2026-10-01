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

    'accepted' => 'يجب قبول :attribute.',
    'after' => 'يجب أن يكون :attribute تاريخًا لاحقًا للتاريخ :date.',
    'alpha' => 'يجب ألا يحتوي :attribute إلا على حروف.',
    'alpha_dash' => 'يجب ألا يحتوي :attribute إلا على حروف وأرقام وشرطات وشرطات سفلية.',
    'alpha_num' => 'يجب ألا يحتوي :attribute إلا على حروف وأرقام.',
    'array' => 'يجب أن يكون :attribute قائمة.',
    'before' => 'يجب أن يكون :attribute تاريخًا سابقًا للتاريخ :date.',
    'between' => [
        'array' => 'يجب أن يحتوي :attribute على عدد من العناصر بين :min و :max.',
        'file' => 'يجب أن يكون حجم :attribute بين :min و :max كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة :attribute بين :min و :max.',
        'string' => 'يجب أن يكون طول :attribute بين :min و :max حرفًا.',
    ],
    'boolean' => 'يجب أن تكون قيمة :attribute صحيحة أو خاطئة.',
    'confirmed' => 'تأكيد :attribute غير مطابق.',
    'current_password' => 'كلمة المرور غير صحيحة.',
    'date' => ':attribute ليس تاريخًا صالحًا.',
    'different' => 'يجب أن يكون :attribute و :other مختلفين.',
    'digits' => 'يجب أن يتكون :attribute من :digits أرقام.',
    'email' => 'يجب أن يكون :attribute عنوان بريد إلكتروني صالحًا.',
    'exists' => 'قيمة :attribute المحددة غير صالحة.',
    'file' => 'يجب أن يكون :attribute ملفًا.',
    'filled' => 'يجب أن يحتوي :attribute على قيمة.',
    'image' => 'يجب أن يكون :attribute صورة.',
    'in' => 'قيمة :attribute المحددة غير صالحة.',
    'integer' => 'يجب أن يكون :attribute عددًا صحيحًا.',
    'lowercase' => 'يجب أن يكون :attribute بأحرف صغيرة.',
    'max' => [
        'array' => 'يجب ألا يحتوي :attribute على أكثر من :max عناصر.',
        'file' => 'يجب ألا يتجاوز حجم :attribute :max كيلوبايت.',
        'numeric' => 'يجب ألا تتجاوز قيمة :attribute :max.',
        'string' => 'يجب ألا يتجاوز طول :attribute :max حرفًا.',
    ],
    'mimes' => 'يجب أن يكون :attribute ملفًا من نوع: :values.',
    'min' => [
        'array' => 'يجب أن يحتوي :attribute على :min عناصر على الأقل.',
        'file' => 'يجب أن يكون حجم :attribute :min كيلوبايت على الأقل.',
        'numeric' => 'يجب أن تكون قيمة :attribute :min على الأقل.',
        'string' => 'يجب أن يتكون :attribute من :min أحرف على الأقل.',
    ],
    'not_in' => 'قيمة :attribute المحددة غير صالحة.',
    'numeric' => 'يجب أن يكون :attribute رقمًا.',
    'password' => [
        'letters' => 'يجب أن يحتوي :attribute على حرف واحد على الأقل.',
        'mixed' => 'يجب أن يحتوي :attribute على حرف كبير وحرف صغير على الأقل.',
        'numbers' => 'يجب أن يحتوي :attribute على رقم واحد على الأقل.',
        'symbols' => 'يجب أن يحتوي :attribute على رمز واحد على الأقل.',
        'uncompromised' => 'ظهر :attribute المُدخل في تسريب بيانات. يُرجى اختيار :attribute مختلف.',
    ],
    'regex' => 'صيغة :attribute غير صالحة.',
    'required' => 'حقل :attribute مطلوب.',
    'required_if' => 'حقل :attribute مطلوب عندما يكون :other هو :value.',
    'required_with' => 'حقل :attribute مطلوب عند وجود :values.',
    'same' => 'يجب أن يتطابق :attribute مع :other.',
    'size' => [
        'array' => 'يجب أن يحتوي :attribute على :size عناصر.',
        'file' => 'يجب أن يكون حجم :attribute :size كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة :attribute :size.',
        'string' => 'يجب أن يتكون :attribute من :size أحرف.',
    ],
    'string' => 'يجب أن يكون :attribute نصًا.',
    'unique' => ':attribute مستخدم من قبل.',
    'uploaded' => 'فشل تحميل :attribute.',
    'url' => 'يجب أن يكون :attribute رابطًا صالحًا.',
    'uuid' => 'يجب أن يكون :attribute معرّف UUID صالحًا.',

    'attributes' => [
        'name' => 'الاسم',
        'email' => 'البريد الإلكتروني',
        'password' => 'كلمة المرور',
        'current_password' => 'كلمة المرور الحالية',
        'code' => 'الرمز',
        'recovery_code' => 'رمز الاسترداد',
        'locale' => 'اللغة',
        'form.name' => 'الاسم',
        'form.email' => 'البريد الإلكتروني',
        'form.password' => 'كلمة المرور',
        'form.roles' => 'الأدوار',
        'form.roles.*' => 'الدور',
        'form.locale' => 'اللغة',
        'form.permissions' => 'الصلاحيات',
        'form.permissions.*' => 'الصلاحية',
    ],

];
