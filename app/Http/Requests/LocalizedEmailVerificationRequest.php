<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LocalizedEmailVerificationRequest extends FormRequest
{
    public function authorize()
    {
        //        \Log::debug('🔍 LocalizedEmailVerificationRequest authorize() debug', [
        //            'route_id' => $this->route('id'),
        //            'user_id' => $this->user()->getKey(),
        //            'route_hash' => $this->route('hash'),
        //            'expected_hash' => sha1($this->user()->getEmailForVerification()),
        //            'id_match' => hash_equals((string) $this->route('id'), (string) $this->user()->getKey()),
        //            'hash_match' => hash_equals((string) $this->route('hash'), sha1($this->user()->getEmailForVerification())),
        //        ]);

        if (! hash_equals((string) $this->route('id'), (string) $this->user()->getKey())) {
            //            \Log::debug('❌ ID mismatch in authorization');
            return false;
        }

        if (! hash_equals((string) $this->route('hash'), sha1($this->user()->getEmailForVerification()))) {
            //            \Log::debug('❌ Hash mismatch in authorization');
            return false;
        }

        //        \Log::debug('✅ Authorization successful');
        return true;
    }

    public function rules()
    {
        return [
            //
        ];
    }
}
