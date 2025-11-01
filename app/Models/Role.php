<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    /**
     * ✅ CORRECTION PRIORITAIRE - Ajouter is_system dans fillable
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'color',
        'level',
        'is_active',
        'is_system'  // ← CRITIQUE: Était probablement manquant
    ];

    /**
     * ✅ CORRECTION - Casts complets
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',  // ← Important pour la conversion
        'level' => 'integer',
    ];

    /**
     * ✅ AJOUT - Valeurs par défaut pour éviter les erreurs
     */
    protected $attributes = [
        'is_active' => true,
        'is_system' => false,
        'color' => '#009e3f',
        'level' => 1
    ];

    /**
     * Les attributs qui doivent être ajoutés aux tableaux.
     */
    protected $appends = ['users_count', 'permissions_count'];

    /**
     * ✅ MÉTHODE CORRIGÉE - Vérifier si c'est un rôle système
     */
    public function isSystemRole()
    {
        // Vérifier d'abord le champ is_system
        if (isset($this->attributes['is_system'])) {
            return (bool) $this->is_system;
        }
        
        // Fallback: vérifier par le nom si le champ n'est pas défini
        $systemRoles = array_keys(self::getSystemRoles());
        return in_array($this->name, $systemRoles);
    }

    // =================================================================
    // CONSTANTES - RÔLES SYSTÈME PNGDI
    // =================================================================

    const SUPER_ADMIN = 'super_admin';
    const ADMIN_GENERAL = 'admin_general';
    const ADMIN_ASSOCIATIONS = 'admin_associations';
    const ADMIN_RELIGIEUSES = 'admin_religieuses';
    const ADMIN_POLITIQUES = 'admin_politiques';
    const MODERATEUR = 'moderateur';
    const OPERATEUR = 'operateur';
    const AUDITEUR = 'auditeur';

    /**
     * Rôles système prédéfinis avec couleurs gabonaises
     */
    public static function getSystemRoles()
    {
        return [
            self::SUPER_ADMIN => [
                'display_name' => 'Super Administrateur',
                'description' => 'Accès total au système PNGDI - Tous pouvoirs',
                'color' => '#8b1538',
                'level' => 10
            ],
            self::ADMIN_GENERAL => [
                'display_name' => 'Administrateur Général',
                'description' => 'Gestion globale de toutes les organisations',
                'color' => '#003f7f',
                'level' => 9
            ],
            self::ADMIN_ASSOCIATIONS => [
                'display_name' => 'Admin Associations',
                'description' => 'Gestion spécialisée des organisations associatives',
                'color' => '#009e3f',
                'level' => 8
            ],
            self::ADMIN_RELIGIEUSES => [
                'display_name' => 'Admin Religieuses',
                'description' => 'Gestion spécialisée des confessions religieuses',
                'color' => '#ffcd00',
                'level' => 8
            ],
            self::ADMIN_POLITIQUES => [
                'display_name' => 'Admin Politiques',
                'description' => 'Gestion spécialisée des partis politiques',
                'color' => '#007bff',
                'level' => 8
            ],
            self::MODERATEUR => [
                'display_name' => 'Modérateur',
                'description' => 'Validation et modération des contenus',
                'color' => '#17a2b8',
                'level' => 6
            ],
            self::OPERATEUR => [
                'display_name' => 'Opérateur',
                'description' => 'Saisie et consultation des données',
                'color' => '#28a745',
                'level' => 4
            ],
            self::AUDITEUR => [
                'display_name' => 'Auditeur',
                'description' => 'Consultation uniquement - Accès lecture seule',
                'color' => '#6c757d',
                'level' => 2
            ]
        ];
    }

    // =================================================================
    // RELATIONS
    // =================================================================

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id')
                    ->withTimestamps();
    }

    // =================================================================
    // SCOPES
    // =================================================================

    public function scopeOrderByLevel($query, $direction = 'desc')
    {
        return $query->orderBy('level', $direction);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('display_name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    // =================================================================
    // ACCESSEURS
    // =================================================================

    public function getUsersCountAttribute()
    {
        if ($this->relationLoaded('users')) {
            return $this->users->count();
        }
        return $this->users()->count();
    }

    public function getPermissionsCountAttribute()
    {
        if ($this->relationLoaded('permissions')) {
            return $this->permissions->count();
        }
        return $this->permissions()->count();
    }

    public function getColorAttribute($value)
    {
        return $value ?: '#009e3f';
    }

    // =================================================================
    // MUTATEURS
    // =================================================================

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtolower(str_replace(' ', '_', $value));
    }

    public function setColorAttribute($value)
    {
        if (preg_match('/^#[a-f0-9]{6}$/i', $value)) {
            $this->attributes['color'] = strtolower($value);
        } else {
            $this->attributes['color'] = '#009e3f';
        }
    }

    // =================================================================
    // MÉTHODES PERMISSIONS
    // =================================================================

    public function givePermission($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->first();
        }
        
        if ($permission && !$this->hasPermission($permission->name)) {
            $this->permissions()->attach($permission->id);
        }
        
        return $this;
    }

    public function hasPermission($permissionName)
    {
        return $this->permissions()
                    ->where('name', $permissionName)
                    ->exists();
    }

    public function syncPermissions(array $permissions)
    {
        if (is_array($permissions) && !empty($permissions)) {
            if (is_string($permissions[0])) {
                $permissionIds = Permission::whereIn('name', $permissions)->pluck('id');
            } else {
                $permissionIds = $permissions;
            }
            
            $this->permissions()->sync($permissionIds);
        } else {
            $this->permissions()->sync([]);
        }
        
        return $this;
    }

    // =================================================================
    // MÉTHODES UTILITAIRES
    // =================================================================

    public function canBeDeleted()
    {
        return !$this->isSystemRole() && $this->users()->count() === 0;
    }

    public function isAdminLevel()
    {
        return $this->level >= 8;
    }

    // =================================================================
    // MÉTHODES STATIQUES
    // =================================================================

    public static function createSystemRole($name, $data = null)
    {
        $systemRoles = self::getSystemRoles();
        
        if (!isset($systemRoles[$name])) {
            throw new \InvalidArgumentException("Rôle système {$name} non défini");
        }
        
        $roleData = $systemRoles[$name];
        
        if ($data) {
            $roleData = array_merge($roleData, $data);
        }
        
        return self::create([
            'name' => $name,
            'display_name' => $roleData['display_name'],
            'description' => $roleData['description'],
            'color' => $roleData['color'],
            'level' => $roleData['level'],
            'is_active' => true,
            'is_system' => true  // ← Important
        ]);
    }
}