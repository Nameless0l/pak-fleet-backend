<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Seul le chef de service peut ajouter/modifier des véhicules
        return $this->user()->isChief();
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convertir under_warranty en booléen
        if ($this->has('under_warranty')) {
            $this->merge([
                'under_warranty' => filter_var($this->under_warranty, FILTER_VALIDATE_BOOLEAN)
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'registration_number' => ['required', 'string', 'max:255'],
            'brand' => ['required', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:255'],
            'vehicle_type_id' => ['required', 'exists:vehicle_types,id'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'acquisition_date' => ['nullable', 'date', 'before_or_equal:today'],
            'status' => ['required', 'in:active,maintenance,out_of_service'],
            'under_warranty' => ['boolean'],
            'warranty_end_date' => ['nullable', 'date', 'after:today'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'], // 5MB max
        ];

        // Pour la mise à jour, rendre l'unicité conditionnelle
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $vehicleId = $this->route('vehicle')->id ?? $this->route('vehicle');
            $rules['registration_number'][] = "unique:vehicles,registration_number,{$vehicleId}";
        } else {
            $rules['registration_number'][] = 'unique:vehicles,registration_number';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'registration_number.required' => 'Le numéro d\'immatriculation est obligatoire.',
            'registration_number.unique' => 'Ce numéro d\'immatriculation existe déjà.',
            'brand.required' => 'La marque est obligatoire.',
            'model.required' => 'Le modèle est obligatoire.',
            'vehicle_type_id.required' => 'Le type de véhicule est obligatoire.',
            'vehicle_type_id.exists' => 'Le type de véhicule sélectionné n\'existe pas.',
            'year.min' => 'L\'année doit être supérieure à 1900.',
            'year.max' => 'L\'année ne peut pas être dans le futur.',
            'acquisition_date.before_or_equal' => 'La date d\'acquisition ne peut pas être dans le futur.',
            'warranty_end_date.after' => 'La date de fin de garantie doit être dans le futur.',
            'image.image' => 'Le fichier doit être une image.',
            'image.mimes' => 'L\'image doit être au format : jpeg, png, jpg ou gif.',
            'image.max' => 'L\'image ne doit pas dépasser 5MB.',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->under_warranty && !$this->warranty_end_date) {
                $validator->errors()->add('warranty_end_date', 'La date de fin de garantie est obligatoire si le véhicule est sous garantie.');
            }
        });
    }
}