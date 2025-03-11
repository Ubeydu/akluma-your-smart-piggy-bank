<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attribute alanı kabul edilmelidir.',
    'accepted_if' => ':attribute alanı, :other :value olduğunda kabul edilmelidir.',
    'active_url' => ':attribute geçerli bir URL olmalıdır.',
    'after' => ':attribute, :date tarihinden sonraki bir tarih olmalıdır.',
    'after_or_equal' => ':attribute, :date tarihine eşit veya daha sonraki bir tarih olmalıdır.',
    'alpha' => ':attribute yalnızca harflerden oluşmalıdır.',
    'alpha_dash' => ':attribute yalnızca harfler, sayılar, tireler ve alt çizgiler içermelidir.',
    'alpha_num' => ':attribute yalnızca harfler ve sayılar içermelidir.',
    'array' => ':attribute bir dizi olmalıdır.',
    'ascii' => ':attribute yalnızca tek baytlık alfanümerik karakterler ve semboller içermelidir.',
    'before' => ':attribute, :date tarihinden önceki bir tarih olmalıdır.',
    'before_or_equal' => ':attribute, :date tarihine eşit veya daha önceki bir tarih olmalıdır.',
    'between' => [
        'array' => ':attribute :min ile :max arasında öğe içeriyor olmalıdır.',
        'file' => ':attribute, :min ile :max kilobayt arasında olmalıdır.',
        'numeric' => ':attribute, :min ile :max arasında olmalıdır.',
        'string' => ':attribute, :min ile :max karakter arasında olmalıdır.',
    ],
    'boolean' => ':attribute alanı doğru veya yanlış olmalıdır.',
    'can' => ':attribute yetkisiz bir değer içermektedir.',
    'confirmed' => ':attribute onayı eşleşmiyor.',
    'contains' => ':attribute gerekli bir değeri içermemektedir.',
    'current_password' => 'şifre yanlış.',
    'date' => ':attribute geçerli bir tarih olmalıdır.',
    'date_equals' => ':attribute, :date ile eşit bir tarih olmalıdır.',
    'date_format' => ':attribute, :format formatıyla eşleşmelidir.',
    'decimal' => ':attribute :decimal ondalık haneye sahip olmalıdır.',
    'declined' => ':attribute reddedilmelidir.',
    'declined_if' => ':attribute, :other :value olduğunda reddedilmelidir.',
    'different' => ':attribute ile :other farklı olmalıdır.',
    'digits' => ':attribute :digits basamak olmalıdır.',
    'digits_between' => ':attribute, :min ile :max basamak arasında olmalıdır.',
    'dimensions' => ':attribute geçersiz resim boyutlarına sahiptir.',
    'distinct' => ':attribute alanı tekrarlanan bir değere sahiptir.',
    'doesnt_end_with' => ':attribute aşağıdakilerden biriyle bitmemelidir: :values.',
    'doesnt_start_with' => ':attribute aşağıdakilerden biriyle başlamamalıdır: :values.',
    'email' => ':attribute geçerli bir e-posta adresi olmalıdır.',
    'ends_with' => ':attribute aşağıdakilerden biriyle bitmelidir: :values.',
    'enum' => 'Seçilen :attribute geçersiz.',
    'exists' => 'Seçilen :attribute geçersiz.',
    'extensions' => ':attribute aşağıdaki uzantılardan birine sahip olmalıdır: :values.',
    'file' => ':attribute bir dosya olmalıdır.',
    'filled' => ':attribute alanı bir değere sahip olmalıdır.',
    'gt' => [
        'array' => ':attribute :value öğesinden fazla içermelidir.',
        'file' => ':attribute, :value kilobayttan büyük olmalıdır.',
        'numeric' => ':attribute, :value\'den büyük olmalıdır.',
        'string' => ':attribute, :value karakterden büyük olmalıdır.',
    ],
    'gte' => [
        'array' => ':attribute :value öğe veya daha fazlasını içermelidir.',
        'file' => ':attribute, :value kilobayttan büyük veya eşit olmalıdır.',
        'numeric' => ':attribute, :value\'den büyük veya eşit olmalıdır.',
        'string' => ':attribute, :value karakterden büyük veya eşit olmalıdır.',
    ],
    'hex_color' => ':attribute geçerli bir onaltılık renk olmalıdır.',
    'image' => ':attribute bir resim olmalıdır.',
    'in' => 'Seçilen :attribute geçersiz.',
    'in_array' => ':attribute :other içinde mevcut olmalıdır.',
    'integer' => ':attribute bir tamsayı olmalıdır.',
    'ip' => ':attribute geçerli bir IP adresi olmalıdır.',
    'ipv4' => ':attribute geçerli bir IPv4 adresi olmalıdır.',
    'ipv6' => ':attribute geçerli bir IPv6 adresi olmalıdır.',
    'json' => ':attribute geçerli bir JSON dizesi olmalıdır.',
    'list' => ':attribute bir liste olmalıdır.',
    'lowercase' => ':attribute küçük harf olmalıdır.',
    'lt' => [
        'array' => ':attribute :value öğesinden az içermelidir.',
        'file' => ':attribute, :value kilobayttan küçük olmalıdır.',
        'numeric' => ':attribute, :value\'den küçük olmalıdır.',
        'string' => ':attribute, :value karakterden küçük olmalıdır.',
    ],
    'lte' => [
        'array' => ':attribute :value öğesinden fazla olamaz.',
        'file' => ':attribute, :value kilobayttan küçük veya eşit olmalıdır.',
        'numeric' => ':attribute, :value\'den küçük veya eşit olmalıdır.',
        'string' => ':attribute, :value karakterden küçük veya eşit olmalıdır.',
    ],
    'mac_address' => ':attribute geçerli bir MAC adresi olmalıdır.',
    'max' => [
        'array' => ':attribute :max öğesinden fazla içermemelidir.',
        'file' => ':attribute, :max kilobayttan büyük olmamalıdır.',
        'numeric' => ':attribute, :max\'dan büyük olmamalıdır.',
        'string' => ':attribute, :max karakterden büyük olmamalıdır.',
    ],
    'max_digits' => ':attribute :max basamaktan fazla olamaz.',
    'mimes' => ':attribute şu türde bir dosya olmalıdır: :values.',
    'mimetypes' => ':attribute şu türde bir dosya olmalıdır: :values.',
    'min' => [
        'array' => ':attribute en az :min öğe içermelidir.',
        'file' => ':attribute en az :min kilobayt olmalıdır.',
        'numeric' => ':attribute en az :min olmalıdır.',
        'string' => ':attribute en az :min karakter olmalıdır.',
    ],
    'min_digits' => ':attribute en az :min basamak olmalıdır.',
    'missing' => ':attribute eksik olmalıdır.',
    'missing_if' => ':attribute, :other :value olduğunda eksik olmalıdır.',
    'missing_unless' => ':attribute, :other :value değilse eksik olmalıdır.',
    'missing_with' => ':attribute, :values mevcut olduğunda eksik olmalıdır.',
    'missing_with_all' => ':attribute, :values mevcut olduğunda eksik olmalıdır.',
    'multiple_of' => ':attribute, :value\'nin katı olmalıdır.',
    'not_in' => 'Seçilen :attribute geçersiz.',
    'not_regex' => ':attribute formatı geçersiz.',
    'numeric' => ':attribute bir sayı olmalıdır.',
    'password' => [
        'letters' => ':attribute en az bir harf içermelidir.',
        'mixed' => ':attribute en az bir büyük harf ve bir küçük harf içermelidir.',
        'numbers' => ':attribute en az bir rakam içermelidir.',
        'symbols' => ':attribute en az bir sembol içermelidir.',
        'uncompromised' => 'Verilen :attribute bir veri ihlalinde yer almış. Lütfen farklı bir :attribute seçin.',
    ],
    'present' => ':attribute mevcut olmalıdır.',
    'present_if' => ':attribute, :other :value olduğunda mevcut olmalıdır.',
    'present_unless' => ':attribute, :other :value değilse mevcut olmalıdır.',
    'present_with' => ':attribute, :values mevcut olduğunda mevcut olmalıdır.',
    'present_with_all' => ':attribute, :values mevcut olduğunda mevcut olmalıdır.',
    'prohibited' => ':attribute yasaklanmıştır.',
    'prohibited_if' => ':attribute, :other :value olduğunda yasaklanmıştır.',
    'prohibited_unless' => ':attribute yasaklanmıştır, :other :values içinde değilse.',
    'prohibits' => ':attribute, :other\'in mevcut olmasını engeller.',
    'regex' => ':attribute formatı geçersiz.',
    'required' => ':attribute gerekli.',
    'required_array_keys' => ':attribute, :values için girişler içermelidir.',
    'required_if' => ':attribute, :other, :value olduğunda gereklidir.',
    'required_if_accepted' => ':other kabul edildiğinde :attribute gereklidir.',
    'required_if_declined' => ':other reddedildiğinde :attribute gereklidir.',
    'required_unless' => ':attribute gereklidir, :other :values içinde değilse.',
    'required_with' => ':attribute, :values mevcut olduğunda gereklidir.',
    'required_with_all' => ':attribute, :values mevcut olduğunda gereklidir.',
    'required_without' => ':attribute, :values mevcut olmadığında gereklidir.',
    'required_without_all' => ':attribute, :values mevcut olmadığında gereklidir.',
    'same' => ':attribute, :other ile eşleşmelidir.',
    'size' => [
        'array' => ':attribute :size öğe içermelidir.',
        'file' => ':attribute :size kilobayt olmalıdır.',
        'numeric' => ':attribute :size olmalıdır.',
        'string' => ':attribute :size karakter olmalıdır.',
    ],
    'starts_with' => ':attribute aşağıdakilerden biriyle başlamalıdır: :values.',
    'string' => ':attribute bir dize olmalıdır.',
    'timezone' => ':attribute geçerli bir saat dilimi olmalıdır.',
    'unique' => ':attribute zaten alınmış.',
    'uploaded' => ':attribute yüklenemedi.',
    'uppercase' => ':attribute büyük harf olmalıdır.',
    'url' => ':attribute geçerli bir URL olmalıdır.',
    'ulid' => ':attribute geçerli bir ULID olmalıdır.',
    'uuid' => ':attribute geçerli bir UUID olmalıdır.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'name' => 'Almak istediğin şeyin adı',
        'price_whole' => 'Fiyat bilgisi',
        'price_cents' => 'kuruş',
        'starting_amount_whole' => 'başlangıç miktarı',
        'starting_amount_cents' => 'başlangıç kuruşu',
        'currency' => 'para birimi',
        'link' => 'bağlantı',
        'details' => 'detaylar',
        'password' => 'şifre',
    ],

];
