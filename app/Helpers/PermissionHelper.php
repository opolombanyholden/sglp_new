<?php
// app/Helpers/PermissionHelper.php

if (!function_exists('getCategoryColor')) {
    function getCategoryColor($category) {
        $colors = [
            'users' => 'linear-gradient(135deg, #003f7f, #0056b3)',
            'organizations' => 'linear-gradient(135deg, #009e3f, #00b347)',
            'workflow' => 'linear-gradient(135deg, #ffcd00, #fd7e14)',
            'system' => 'linear-gradient(135deg, #e74a3b, #c23321)',
            'content' => 'linear-gradient(135deg, #36b9cc, #258391)',
            'reports' => 'linear-gradient(135deg, #6f42c1, #5a32a3)',
            'api' => 'linear-gradient(135deg, #fd7e14, #e55a00)'
        ];
        return $colors[$category] ?? 'linear-gradient(135deg, #009e3f, #00b347)';
    }
}

if (!function_exists('getCategoryIcon')) {
    function getCategoryIcon($category) {
        $icons = [
            'users' => 'fas fa-users',
            'organizations' => 'fas fa-building',
            'workflow' => 'fas fa-tasks',
            'system' => 'fas fa-cogs',
            'content' => 'fas fa-file-alt',
            'reports' => 'fas fa-chart-line',
            'api' => 'fas fa-code'
        ];
        return $icons[$category] ?? 'fas fa-key';
    }
}

if (!function_exists('getCategoryLabel')) {
    function getCategoryLabel($category) {
        $labels = [
            'users' => 'Gestion Utilisateurs',
            'organizations' => 'Gestion Organisations',
            'workflow' => 'Gestion Workflow',
            'system' => 'Gestion Système',
            'content' => 'Gestion Contenus',
            'reports' => 'Rapports et Analytics',
            'api' => 'API et Intégrations'
        ];
        return $labels[$category] ?? ucfirst($category);
    }
}

if (!function_exists('getCategoryDescription')) {
    function getCategoryDescription($category) {
        $descriptions = [
            'users' => 'Permissions pour la gestion des comptes utilisateurs et des accès',
            'organizations' => 'Permissions pour la gestion des organisations et des entités',
            'workflow' => 'Permissions pour la validation et les processus métier',
            'system' => 'Permissions système et de configuration avancée',
            'content' => 'Permissions pour la gestion du contenu et des médias',
            'reports' => 'Permissions pour l\'accès aux rapports et statistiques',
            'api' => 'Permissions pour l\'accès aux APIs et intégrations'
        ];
        return $descriptions[$category] ?? 'Permissions de cette catégorie';
    }
}

if (!function_exists('isSystemCategory')) {
    function isSystemCategory($category) {
        return in_array($category, ['system', 'api']);
    }
}

if (!function_exists('getCategoryIconColor')) {
    function getCategoryIconColor($category) {
        $colors = [
            'users' => '#003f7f',
            'organizations' => '#009e3f',
            'workflow' => '#ffcd00',
            'system' => '#e74a3b',
            'content' => '#36b9cc',
            'reports' => '#6f42c1',
            'api' => '#fd7e14'
        ];
        return $colors[$category] ?? '#009e3f';
    }
}

if (!function_exists('getCategoryUsersCount')) {
    function getCategoryUsersCount($category) {
        // À implémenter selon votre logique métier
        // Exemple de requête pour compter les utilisateurs par catégorie de permissions
        return \DB::table('role_user')
            ->join('roles', 'role_user.role_id', '=', 'roles.id')
            ->join('role_has_permissions', 'roles.id', '=', 'role_has_permissions.role_id')
            ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
            ->where('permissions.category', $category)
            ->distinct('role_user.user_id')
            ->count();
    }
}

if (!function_exists('getCategoryRolesCount')) {
    function getCategoryRolesCount($category) {
        // À implémenter selon votre logique métier
        // Exemple de requête pour compter les rôles par catégorie de permissions
        return \DB::table('roles')
            ->join('role_has_permissions', 'roles.id', '=', 'role_has_permissions.role_id')
            ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
            ->where('permissions.category', $category)
            ->distinct('roles.id')
            ->count();
    }
}