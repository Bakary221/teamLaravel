<?php

namespace App\Http\Requests;

use App\Rules\ValidNciSenegal;
use App\Rules\ValidTelephoneSenegal;
use Illuminate\Foundation\Http\FormRequest;

// CreateCompteRequest pour valider les données d'entrée lors de la création d'un compte.
class CreateCompteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'type' => 'required|in:cheque,epargne',
            'soldeInitial' => 'required|numeric|min:10000',
            'devise' => 'required|string|in:FCFA',
            'solde' => 'required|numeric|min:10000',
            'client' => 'required|array',
            'client.id' => 'nullable|uuid|exists:clients,id',
            'client.profession' => 'nullable|string|max:255',
        ];

        if (!$this->input('client.id')) {
            // Si aucun client.id n'est fourni, les champs client sont requis pour créer un nouveau client
            $rules = array_merge($rules, [
                'client.titulaire' => 'required|string|min:2|max:255',
                'client.nci' => ['required', 'string', new ValidNciSenegal()],
                'client.email' => 'required|email|unique:users,email',
                'client.telephone' => ['required', 'string', new ValidTelephoneSenegal(), 'unique:users,telephone'],
                'client.adresse' => 'required|string|min:5|max:500',
            ]);
        } else {
            // Si client.id est fourni, les autres champs sont optionnels
            $rules = array_merge($rules, [
                'client.titulaire' => 'nullable|string|min:2|max:255',
                'client.nci' => 'nullable|string',
                'client.email' => 'nullable|email',
                'client.telephone' => 'nullable|string',
                'client.adresse' => 'nullable|string|min:5|max:500',
            ]);
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Le type de compte est obligatoire.',
            'type.in' => 'Le type de compte doit être soit "cheque" soit "epargne".',
            'soldeInitial.required' => 'Le solde initial est obligatoire.',
            'soldeInitial.numeric' => 'Le solde initial doit être un nombre.',
            'soldeInitial.min' => 'Le solde initial doit être supérieur ou égal à 10 000.',
            'devise.required' => 'La devise est obligatoire.',
            'devise.in' => 'La devise doit être "FCFA".',
            'solde.required' => 'Le solde est obligatoire.',
            'solde.numeric' => 'Le solde doit être un nombre.',
            'solde.min' => 'Le solde doit être supérieur ou égal à 10 000.',
            'client.required' => 'Les informations du client sont obligatoires.',
            'client.array' => 'Les informations du client doivent être un objet.',
            'client.id.uuid' => 'L\'ID du client doit être un UUID valide.',
            'client.id.exists' => 'Le client spécifié n\'existe pas.',
            'client.titulaire.required' => 'Le nom du titulaire est obligatoire.',
            'client.titulaire.min' => 'Le nom du titulaire doit contenir au moins 2 caractères.',
            'client.titulaire.max' => 'Le nom du titulaire ne peut pas dépasser 255 caractères.',
            'client.nci.required' => 'Le numéro NCI est obligatoire.',
            'client.email.required' => 'L\'email est obligatoire.',
            'client.email.email' => 'L\'email doit être valide.',
            'client.email.unique' => 'Cet email est déjà utilisé.',
            'client.telephone.required' => 'Le numéro de téléphone est obligatoire.',
            'client.telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'client.adresse.required' => 'L\'adresse est obligatoire.',
            'client.adresse.min' => 'L\'adresse doit contenir au moins 5 caractères.',
            'client.adresse.max' => 'L\'adresse ne peut pas dépasser 500 caractères.',
            'client.profession.max' => 'La profession ne peut pas dépasser 255 caractères.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'type' => 'type de compte',
            'soldeInitial' => 'solde initial',
            'devise' => 'devise',
            'solde' => 'solde',
            'client.id' => 'ID du client',
            'client.titulaire' => 'nom du titulaire',
            'client.nci' => 'numéro NCI',
            'client.email' => 'email',
            'client.telephone' => 'numéro de téléphone',
            'client.adresse' => 'adresse',
            'client.profession' => 'profession',
        ];
    }
}
