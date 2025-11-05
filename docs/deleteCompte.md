# Documentation : Endpoint de suppression (soft delete) d’un compte

## Objectif
Mettre en place l’endpoint `DELETE /api/v1/comptes/{compteId}` pour permettre à un administrateur de supprimer (soft delete) un compte bancaire, conformément à l’US 2.4.

---

## 1. Migration : Ajout du soft delete et de la date de fermeture

Dans le fichier de migration de la table `comptes` :

```php
// database/migrations/2025_11_03_160400_create_comptes_table.php
$table->timestamp('date_fermeture')->nullable();
$table->softDeletes();
```

---

## 2. Modèle Compte : Activation du soft delete

Dans le modèle `Compte` :

```php
use Illuminate\Database\Eloquent\SoftDeletes;

class Compte extends Model
{
    use HasFactory, SoftDeletes;
    // ...
    protected $fillable = [
        'client_id',
        'numero_compte',
        'type',
        'statut',
        'motif_blocage',
        'date_fermeture',
    ];
    // ...
}
```

---

## 3. Contrôleur : Méthode destroy

Dans `CompteController.php`, ajouter la méthode suivante dans la classe :

```php
public function destroy($id): JsonResponse
{
    try {
        $compte = Compte::withoutGlobalScopes()->findOrFail($id);
        if ($compte->statut === 'fermé') {
            return $this->errorResponse('Ce compte est déjà fermé.', 400);
        }
        $compte->statut = 'fermé';
        $compte->date_fermeture = now();
        $compte->save();
        $compte->delete();

        return $this->successResponse([
            'id' => $compte->id,
            'numeroCompte' => $compte->numero_compte,
            'statut' => $compte->statut,
            'dateFermeture' => $compte->date_fermeture ? $compte->date_fermeture->toISOString() : null,
        ], 'Compte supprimé avec succès');
    } catch (\Exception $e) {
        return $this->errorResponse('Erreur lors de la suppression du compte', 500);
    }
}
```

---

## 4. Route API

Dans `routes/api.php`, ajouter la route suivante dans le groupe v1 :

```php
Route::delete('comptes/{compte}', [CompteController::class, 'destroy']);
```

---

## 5. Migration de la base de données

Exécuter la commande suivante pour appliquer les modifications de la migration :

```bash
php artisan migrate
```

---

## 6. Exemple de requête et de réponse

### Requête

```
DELETE /api/v1/comptes/550e8400-e29b-41d4-a716-446655440000
Authorization: Bearer {token}
Accept: application/json
```

### Réponse (200 OK)

```json
{
  "success": true,
  "message": "Compte supprimé avec succès",
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "numeroCompte": "C00123456",
    "statut": "fermé",
    "dateFermeture": "2025-10-19T11:15:00Z"
  }
}
```

---

## Résumé
- Soft delete avec statut "fermé" et date de fermeture.
- Réponse structurée conforme à l’US.
- Sécurité via middleware `auth:api`.

> Cette documentation couvre uniquement la mise en place de l’endpoint de suppression d’un compte.
