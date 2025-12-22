<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UstozRegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Any authenticated user can register as ustoz
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|regex:/^\+998[0-9]{9}$/',
            'telegram' => 'nullable|string|max:255',
            'bio' => 'required|string|max:1000',
            'education' => 'required|string|max:500',
            'experience_years' => 'required|integer|min:0|max:50',
            'location' => 'required|string|max:255',
            'center_name' => 'nullable|string|max:255',
            'certificates' => 'nullable|array',
            'certificates.*' => 'string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'To\'liq ism majburiy',
            'full_name.max' => 'Ism 255 belgidan oshmasligi kerak',
            'phone.required' => 'Telefon raqam majburiy',
            'phone.regex' => 'Telefon raqam formati: +998901234567',
            'bio.required' => 'O\'zingiz haqingizda ma\'lumot majburiy',
            'bio.max' => 'Biografiya 1000 belgidan oshmasligi kerak',
            'education.required' => 'Ta\'lim ma\'lumoti majburiy',
            'education.max' => 'Ta\'lim ma\'lumoti 500 belgidan oshmasligi kerak',
            'experience_years.required' => 'Tajriba yili majburiy',
            'experience_years.integer' => 'Tajriba raqam bo\'lishi kerak',
            'experience_years.min' => 'Tajriba 0 dan kichik bo\'lmasligi kerak',
            'experience_years.max' => 'Tajriba 50 yildan oshmasligi kerak',
            'location.required' => 'Joylashuv majburiy',
            'location.max' => 'Joylashuv 255 belgidan oshmasligi kerak',
            'center_name.max' => 'Markaz nomi 255 belgidan oshmasligi kerak',
            'certificates.array' => 'Sertifikatlar ro\'yxat formatida bo\'lishi kerak',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert certificates to array if it's a string
        if ($this->has('certificates') && is_string($this->certificates)) {
            $this->merge([
                'certificates' => json_decode($this->certificates, true) ?? [],
            ]);
        }
    }
}
