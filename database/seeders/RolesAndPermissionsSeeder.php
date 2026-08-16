<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Laratrust\Models\Role;
use Laratrust\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Définir les rôles
        $roles = ['particulier', 'livreur', 'conducteur', 'admin', 'locateur', 'vendeur'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(
                ['name' => $roleName],
                [
                    'display_name' => ucfirst($roleName),
                    'description' => ucfirst($roleName) . ' role',
                ]
            );
        }

        // Définir les permissions
        $permissions = [
            'deliver_objects' => 'Livrer des objets',
            'transport_passengers' => 'Transporter des passagers',
            'view_dashboard' => 'Voir le dashboard',
            'manage_annonces' => 'Gérer les annonces',
        ];

        foreach ($permissions as $name => $display) {
            Permission::firstOrCreate(
                ['name' => $name],
                [
                    'display_name' => $display,
                    'description' => $display
                ]
            );
        }

        // Attribution permissions
        $livreur = Role::where('name', 'livreur')->first();
        $livreur?->permissions()->syncWithoutDetaching([
            Permission::where('name', 'deliver_objects')->first()->id,
            Permission::where('name', 'transport_passengers')->first()->id,
            Permission::where('name', 'view_dashboard')->first()->id,
        ]);

        $admin = Role::where('name', 'admin')->first();
        $admin?->permissions()->syncWithoutDetaching([
            Permission::where('name', 'deliver_objects')->first()->id,
            Permission::where('name', 'transport_passengers')->first()->id,
            Permission::where('name', 'view_dashboard')->first()->id,
            Permission::where('name', 'manage_annonces')->first()->id,
        ]);

        // Permissions pour vendeur
        $vendeur = Role::where('name', 'vendeur')->first();
        $vendeur?->permissions()->syncWithoutDetaching([
            Permission::where('name', 'view_dashboard')->first()->id,
            Permission::where('name', 'manage_annonces')->first()->id,
        ]);
    }
}