<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VideoUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only ustozlar can upload videos
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
            'video' => 'required|file|mimes:mp4,mov,avi,wmv|max:102400', // 100MB max
            'thumbnail' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048', // 2MB max
        ];

        // If updating and no new video provided, make it optional
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['video'] = 'nullable|file|mimes:mp4,mov,avi,wmv|max:102400';
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
            'title.required' => 'Video sarlavhasi majburiy',
            'title.max' => 'Sarlavha 255 belgidan oshmasligi kerak',
            'description.required' => 'Video tavsifi majburiy',
            'description.max' => 'Tavsif 2000 belgidan oshmasligi kerak',
            'subject.required' => 'Fan nomi majburiy',
            'subject.max' => 'Fan nomi 100 belgidan oshmasligi kerak',
            'video.required' => 'Video fayl majburiy',
            'video.file' => 'Video fayl yuklash kerak',
            'video.mimes' => 'Video faqat mp4, mov, avi, wmv formatida bo\'lishi kerak',
            'video.max' => 'Video hajmi 100MB dan oshmasligi kerak',
            'thumbnail.image' => 'Thumbnail faqat rasm formatida bo\'lishi kerak',
            'thumbnail.mimes' => 'Thumbnail faqat jpeg, jpg, png, webp formatida bo\'lishi kerak',
            'thumbnail.max' => 'Thumbnail hajmi 2MB dan oshmasligi kerak',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'sarlavha',
            'description' => 'tavsif',
            'subject' => 'fan',
            'video' => 'video',
            'thumbnail' => 'thumbnail',
        ];
    }
}
