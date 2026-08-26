<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Laratrust\Models\Role;
use Laratrust\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            'particulier',
            'livreur',
            'conducteur',
            'admin',
            'locateur',
            'vendeur',
            'chauffeur_vtc',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(
                ['name' => $roleName],
                [
                    'display_name' => $roleName === 'chauffeur_vtc'
                        ? 'Chauffeur VTC'
                        : ucfirst($roleName),
                    'description' => $roleName === 'chauffeur_vtc'
                        ? 'Chauffeur VTC role'
                        : ucfirst($roleName) . ' role',
                ]
            );
        }

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

        $vendeur = Role::where('name', 'vendeur')->first();
        $vendeur?->permissions()->syncWithoutDetaching([
            Permission::where('name', 'view_dashboard')->first()->id,
            Permission::where('name', 'manage_annonces')->first()->id,
        ]);

        $chauffeurVtc = Role::where('name', 'chauffeur_vtc')->first();
        $chauffeurVtc?->permissions()->syncWithoutDetaching([
            Permission::where('name', 'transport_passengers')->first()->id,
            Permission::where('name', 'view_dashboard')->first()->id,
        ]);
    }
}