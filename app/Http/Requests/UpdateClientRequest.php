<?php

namespace App\Http\Requests;

use App\Models\Compte;
use App\Rules\ValidTelephoneSenegal;
use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Autoriser la requête (était false)
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('compteId') ? Compte::find($this->route('compteId'))->client->user->id : null; // ID de l'utilisateur lié au compte

        return [
            'titulaire' => 'nullable|string|max:255', // Optionnel
            'telephone' => ['nullable', 'string', new ValidTelephoneSenegal(), 'unique:users,telephone,' . $userId], // Optionnel, unique sauf pour l'utilisateur actuel
            'email' => ['nullable', 'email', 'unique:users,email,' . $userId], // Optionnel, unique
            'nci' => 'nullable|string|max:255', // Optionnel
            // Au moins un champ requis
            'titulaire' => 'required_without_all:telephone,email,nci',
            'telephone' => 'required_without_all:titulaire,email,nci',
            'email' => 'required_without_all:titulaire,telephone,nci',
            'nci' => 'required_without_all:titulaire,telephone,email',
        ];
    }

    public function messages()
    {
        return [
            'telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'telephone.valid' => 'Le numéro de téléphone est invalide.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'required_without_all' => 'Au moins un champ de modification est requis.',
        ];
    }
}
