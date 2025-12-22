<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ElonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only ustozlar can create/update elonlar
        return $this->user() && $this->user()->role === 'ustoz';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'subject' => 'required|string|max:100',
            'subject_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'price' => 'required|numeric|min:0|max:10000000',
            'location' => 'required|string|max:255',
            'center_name' => 'nullable|string|max:255',
            'schedule' => 'required|array',
            'schedule.days' => 'required|array|min:1',
            'schedule.days.*' => 'required|string|in:Dushanba,Seshanba,Chorshanba,Payshanba,Juma,Shanba,Yakshanba',
            'schedule.start_time' => 'nullable|string',
            'schedule.end_time' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:15|max:480',
        ];

        // If updating and no new image provided, make it optional
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['subject_image'] = 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'E\'lon sarlavhasi majburiy',
            'title.max' => 'Sarlavha 255 belgidan oshmasligi kerak',
            'description.required' => 'E\'lon tavsifi majburiy',
            'description.max' => 'Tavsif 2000 belgidan oshmasligi kerak',
            'subject.required' => 'Fan nomi majburiy',
            'subject_image.image' => 'Fan rasmi faqat rasm formatida bo\'lishi kerak',
            'subject_image.max' => 'Rasm hajmi 2MB dan oshmasligi kerak',
            'price.required' => 'Narx majburiy',
            'price.numeric' => 'Narx raqam bo\'lishi kerak',
            'price.min' => 'Narx 0 dan kichik bo\'lmasligi kerak',
            'location.required' => 'Joylashuv majburiy',
            'schedule.required' => 'Dars jadvali majburiy',
            'schedule.days.required' => 'Kamida 1 ta kun tanlang',
            'schedule.days.*.in' => 'Noto\'g\'ri kun tanlandi',
            'duration_minutes.required' => 'Dars davomiyligi majburiy',
            'duration_minutes.min' => 'Dars kamida 15 daqiqa bo\'lishi kerak',
            'duration_minutes.max' => 'Dars 8 soatdan oshmasligi kerak',
        ];
    }
}
