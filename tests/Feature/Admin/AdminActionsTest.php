<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Service;
use App\Models\TypeService;
use App\Models\ContactMessage;
use App\Models\Covoiturage;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminActionsTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::where('role', 'admin')->firstOrFail();
    }

    // ──────────────────────────────────────────────
    // 01 DASHBOARD
    // ──────────────────────────────────────────────
    public function test_01_01_acceder_dashboard(): void
    {
        $r = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $r->assertStatus(200);
    }

    public function test_01_02_kpi_users_affiches(): void
    {
        $r = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $r->assertStatus(200)->assertSeeText('Utilisateurs');
    }

    public function test_01_03_kpi_covoiturages_affiches(): void
    {
        $r = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $r->assertStatus(200)->assertSeeText('Covoiturages');
    }

    public function test_01_04_kpi_livraisons_affiches(): void
    {
        $r = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $r->assertStatus(200)->assertSeeText('Livraisons');
    }

    public function test_01_05_kpi_messages_affiches(): void
    {
        $r = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $r->assertStatus(200)->assertSeeText('Messages');
    }

    public function test_01_06_kpi_services_affiches(): void
    {
        $r = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $r->assertStatus(200)->assertSeeText('Services');
    }

    public function test_01_07_graphe_roles_present(): void
    {
        $r = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $r->assertStatus(200)->assertSee('roleChart');
    }

    public function test_01_08_graphe_inscriptions_present(): void
    {
        $r = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $r->assertStatus(200)->assertSee('inscriptionsChart');
    }

    public function test_01_09_activite_recente_presente(): void
    {
        $r = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $r->assertStatus(200)->assertSeeText('Activité récente');
    }

    public function test_01_10_nouveaux_membres_presents(): void
    {
        $r = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $r->assertStatus(200)->assertSeeText('Nouveaux membres');
    }

    // ──────────────────────────────────────────────
    // 02 UTILISATEURS
    // ──────────────────────────────────────────────
    public function test_02_01_liste_utilisateurs(): void
    {
        $r = $this->actingAs($this->admin)->get(route('admin.users.index'));
        $r->assertStatus(200);
    }

    public function test_02_02_voir_fiche_utilisateur(): void
    {
        $user = User::where('role', '!=', 'admin')->firstOrFail();
        $r = $this->actingAs($this->admin)->get(route('admin.users.show', $user));
        $r->assertStatus(200);
    }

    public function test_02_03_formulaire_creation_utilisateur(): void
    {
        $r = $this->actingAs($this->admin)->get(route('admin.users.create'));
        $r->assertStatus(200);
    }

    public function test_02_04_creer_utilisateur(): void
    {
        $r = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'firstname' => 'Test',
            'lastname'  => 'Seeder',
            'email'     => 'test.seeder.unique@olten.test',
            'password'  => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role'      => 'particulier',
        ]);
        $r->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'test.seeder.unique@olten.test']);
    }

    public function test_02_05_formulaire_edition_utilisateur(): void
    {
        $user = User::where('role', '!=', 'admin')->firstOrFail();
        $r = $this->actingAs($this->admin)->get(route('admin.users.edit', $user));
        $r->assertStatus(200);
    }

    public function test_02_06_modifier_utilisateur(): void
    {
        $user = User::where('role', 'particulier')->firstOrFail();
        $r = $this->actingAs($this->admin)->patch(route('admin.users.update', $user), [
            'firstname' => 'ModifTest',
            'lastname'  => $user->lastname ?? 'Test',
            'email'     => $user->email,
            'role'      => $user->role,
        ]);
        $r->assertRedirect();
    }

    public function test_02_07_supprimer_utilisateur(): void
    {
        $user = User::create([
            'name'      => 'A Supprimer',
            'firstname' => 'A',
            'lastname'  => 'Supprimer',
            'email'     => 'delete.me.test@olten.test',
            'password'  => Hash::make('password'),
            'role'      => 'particulier',
        ]);
        $r = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $user));
        $r->assertRedirect();
        $this->assertDatabaseMissing('users', ['email' => 'delete.me.test@olten.test']);
    }

    public function test_02_08_dashboard_bloque_sans_auth(): void
    {
        $r = $this->get(route('admin.dashboard'));
        $r->assertRedirect();
        $this->assertStringNotContainsString('dashboard', $r->headers->get('Location') ?? 'login');
    }

    public function test_02_09_acces_refuse_non_admin(): void
    {
        $nonAdmin = User::where('role', 'particulier')->firstOrFail();
        $r = $this->actingAs($nonAdmin)->get(route('admin.dashboard'));
        $r->assertStatus(403);
    }

    // ──────────────────────────────────────────────
    // 03 CATÉGORIES
    // ──────────────────────────────────────────────
    public function test_03_01_liste_categories(): void
    {
        $r = $this->actingAs($this->admin)->get(route('admin.categories.index'));
        $r->assertStatus(200);
    }

    public function test_03_02_formulaire_creation_categorie(): void
    {
        $r = $this->actingAs($this->admin)->get(route('admin.categories.create'));
        $r->assertStatus(200);
    }

    public function test_03_03_creer_categorie(): void
    {
        $r = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'nom' => 'Catégorie Test PHPUnit',
        ]);
        $r->assertRedirect();
        $this->assertDatabaseHas('categories', ['nom' => 'Catégorie Test PHPUnit']);
    }

    public function test_03_04_formulaire_edition_categorie(): void
    {
        $cat = Category::firstOrFail();
        $r = $this->actingAs($this->admin)->get(route('admin.categories.edit', $cat));
        $r->assertStatus(200);
    }

    public function test_03_05_modifier_categorie(): void
    {
        $cat = Category::firstOrFail();
        $r = $this->actingAs($this->admin)->put(route('admin.categories.update', $cat), [
            'name' => $cat->name . ' Modifié',
        ]);
        $r->assertRedirect();
    }

    public function test_03_06_supprimer_categorie(): void
    {
        $cat = Category::create(['nom' => 'Catégorie à Supprimer PHPUnit']);
        $r = $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $cat));
        $r->assertRedirect();
        $this->assertDatabaseMissing('categories', ['nom' => 'Catégorie à Supprimer PHPUnit']);
    }

    public function test_03_07_creation_categorie_sans_nom(): void
    {
        $r = $this->actingAs($this->admin)->post(route('admin.categories.store'), ['nom' => '']);
        $r->assertSessionHasErrors('nom');
    }

    // ──────────────────────────────────────────────
    // 04 SOUS-CATÉGORIES
    // ──────────────────────────────────────────────
    public function test_04_01_liste_sous_categories(): void
    {
        $r = $this->actingAs($this->admin)->get(route('admin.subcategories.index'));
        $r->assertStatus(200);
    }

    public function test_04_02_formulaire_creation_sous_categorie(): void
    {
        $r = $this->actingAs($this->admin)->get(route('admin.subcategories.create'));
        $r->assertStatus(200);
    }

    public function test_04_03_creer_sous_categorie(): void
    {
        $cat = Category::firstOrFail();
        $r = $this->actingAs($this->admin)->post(route('admin.subcategories.store'), [
            'nom'         => 'Sous-Cat Test PHPUnit',
            'category_id' => $cat->id,
        ]);
        $r->assertRedirect();
        $this->assertDatabaseHas('subcategories', ['nom' => 'Sous-Cat Test PHPUnit']);
    }

    public function test_04_04_supprimer_sous_categorie(): void
    {
        $cat = Category::firstOrFail();
        $sub = SubCategory::create(['nom' => 'Sous-Cat Delete PHPUnit', 'category_id' => $cat->id]);
        $r = $this->actingAs($this->admin)->delete(route('admin.subcategories.destroy', $sub));
        $r->assertRedirect();
        $this->assertDatabaseMissing('subcategories', ['nom' => 'Sous-Cat Delete PHPUnit']);
    }

    // ──────────────────────────────────────────────
    // 05 TYPES DE SERVICE
    // ──────────────────────────────────────────────
    public function test_05_01_liste_types_service(): void
    {
        $r = $this->actingAs($this->admin)->get(route('admin.type_services.index'));
        $r->assertStatus(200);
    }

    public function test_05_02_creer_type_service(): void
    {
        $r = $this->actingAs($this->admin)->post(route('admin.type_services.store'), [
            'nom' => 'Type Test PHPUnit',
        ]);
        $r->assertRedirect();
        $this->assertDatabaseHas('type_services', ['nom' => 'Type Test PHPUnit']);
    }

    public function test_05_03_modifier_type_service(): void
    {
        $type = TypeService::firstOrFail();
        $r = $this->actingAs($this->admin)->put(route('admin.type_services.update', $type), [
            'nom' => $type->nom . ' Modifié',
        ]);
        $r->assertRedirect();
    }

    public function test_05_04_supprimer_type_service(): void
    {
        $type = TypeService::create(['nom' => 'Type à Supprimer PHPUnit']);
        $r = $this->actingAs($this->admin)->delete(route('admin.type_services.destroy', $type));
        $r->assertRedirect();
        $this->assertDatabaseMissing('type_services', ['nom' => 'Type à Supprimer PHPUnit']);
    }

    // ──────────────────────────────────────────────
    // 06 SERVICES
    // ──────────────────────────────────────────────
    public function test_06_01_liste_services(): void
    {
        $r = $this->actingAs($this->admin)->get(route('admin.services.index'));
        $r->assertStatus(200);
    }

    public function test_06_02_formulaire_creation_service(): void
    {
        $r = $this->actingAs($this->admin)->get(route('admin.services.create'));
        $r->assertStatus(200);
    }

    public function test_06_03_creer_service(): void
    {
        $type = TypeService::firstOrFail();
        $r = $this->actingAs($this->admin)->post(route('admin.services.store'), [
            'nom'             => 'Service Test PHPUnit',
            'description'     => 'Description test',
            'type_service_id' => $type->id,
        ]);
        $r->assertRedirect();
        $this->assertDatabaseHas('services', ['nom' => 'Service Test PHPUnit']);
    }

    public function test_06_04_modifier_service(): void
    {
        $svc = Service::firstOrFail();
        $r = $this->actingAs($this->admin)->put(route('admin.services.update', $svc), [
            'nom'             => $svc->nom . ' Modifié',
            'type_service_id' => $svc->type_service_id,
        ]);
        $r->assertRedirect();
    }

    public function test_06_05_supprimer_service(): void
    {
        $type = TypeService::firstOrFail();
        $svc = Service::create(['nom' => 'Service Delete PHPUnit', 'type_service_id' => $type->id]);
        $r = $this->actingAs($this->admin)->delete(route('admin.services.destroy', $svc));
        $r->assertRedirect();
        $this->assertDatabaseMissing('services', ['nom' => 'Service Delete PHPUnit']);
    }

    // ──────────────────────────────────────────────
    // 07 COVOITURAGES
    // ──────────────────────────────────────────────
    public function test_07_01_liste_covoiturages(): void
    {
        $r = $this->actingAs($this->admin)->get(route('admin.rides.index'));
        $r->assertStatus(200);
    }

    public function test_07_02_filtre_par_statut_pending(): void
    {
        $r = $this->actingAs($this->admin)->get(route('admin.rides.index', ['status' => 'pending']));
        $r->assertStatus(200);
    }

    public function test_07_03_filtre_par_statut_actif(): void
    {
        $r = $this->actingAs($this->admin)->get(route('admin.rides.index', ['status' => 'actif']));
        $r->assertStatus(200);
    }

    public function test_07_04_toggle_statut_pending_vers_actif(): void
    {
        $ride = Covoiturage::where('statut', 'pending')->first();
        if (!$ride) $this->markTestSkipped('Aucun covoiturage pending');
        $r = $this->actingAs($this->admin)->patch(route('admin.rides.toggle-status', $ride));
        $r->assertRedirect();
        $this->assertEquals('actif', $ride->fresh()->statut);
    }

    public function test_07_05_toggle_statut_actif_vers_inactif(): void
    {
        $ride = Covoiturage::where('statut', 'actif')->first();
        if (!$ride) $this->markTestSkipped('Aucun covoiturage actif');
        $r = $this->actingAs($this->admin)->patch(route('admin.rides.toggle-status', $ride));
        $r->assertRedirect();
        $this->assertEquals('inactif', $ride->fresh()->statut);
    }

    // ──────────────────────────────────────────────
    // 08 MESSAGES DE CONTACT
    // ──────────────────────────────────────────────
    public function test_08_01_liste_messages(): void
    {
        $r = $this->actingAs($this->admin)->get(route('admin.contact_messages.index'));
        $r->assertStatus(200);
    }

    public function test_08_02_voir_detail_message(): void
    {
        $msg = ContactMessage::firstOrFail();
        $r = $this->actingAs($this->admin)->get(route('admin.contact_messages.show', $msg));
        $r->assertStatus(200);
    }

    public function test_08_03_supprimer_message(): void
    {
        $msg = ContactMessage::create([
            'name' => 'Test Delete', 'email' => 'del@test.com',
            'subject' => 'Sujet test', 'message' => 'Message test',
        ]);
        $r = $this->actingAs($this->admin)->delete(route('admin.contact_messages.destroy', $msg));
        $r->assertRedirect();
        $this->assertDatabaseMissing('contact_messages', ['id' => $msg->id]);
    }

    public function test_08_04_supprimer_message_inexistant(): void
    {
        $r = $this->actingAs($this->admin)->delete(route('admin.contact_messages.destroy', 99999));
        $r->assertStatus(404);
    }

    // ──────────────────────────────────────────────
    // 09 ANNONCES
    // ──────────────────────────────────────────────
    public function test_09_01_liste_annonces(): void
    {
        $r = $this->actingAs($this->admin)->get(route('admin.admin.ads.index'));
        $r->assertStatus(200);
    }

    public function test_09_02_approuver_annonce(): void
    {
        $ad = \App\Models\Ad::where('is_approved', false)->first();
        if (!$ad) $this->markTestSkipped('Aucune annonce non approuvée');
        $r = $this->actingAs($this->admin)->patch(route('admin.ads.approve', $ad));
        $r->assertRedirect();
        $this->assertEquals(true, $ad->fresh()->is_approved);
    }

    // ──────────────────────────────────────────────
    // 10 CARTES VTC
    // ──────────────────────────────────────────────
    public function test_10_01_liste_vtc(): void
    {
        $r = $this->actingAs($this->admin)->get(route('admin.vtc_cards.index'));
        $r->assertStatus(200);
    }

    public function test_10_02_approuver_document_vtc(): void
    {
        $doc = \App\Models\UserDocument::first();
        if (!$doc) $this->markTestSkipped('Aucun document VTC');
        $r = $this->actingAs($this->admin)->post(route('admin.vtc_cards.approve', $doc));
        $r->assertRedirect();
    }

    public function test_10_03_rejeter_document_vtc(): void
    {
        $doc = \App\Models\UserDocument::first();
        if (!$doc) $this->markTestSkipped('Aucun document VTC');
        $r = $this->actingAs($this->admin)->post(route('admin.vtc_cards.reject', $doc), [
            'rejection_reason' => 'Document illisible',
        ]);
        $r->assertRedirect();
    }

    // ──────────────────────────────────────────────
    // 11 AUTHENTIFICATION
    // ──────────────────────────────────────────────
    public function test_11_01_page_connexion_admin(): void
    {
        $r = $this->get(route('admin.login'));
        $r->assertStatus(200);
    }

    public function test_11_02_connexion_bons_identifiants(): void
    {
        $r = $this->post(route('admin.login.submit'), [
            'email'        => $this->admin->email,
            'mot_de_passe' => 'admin123',
        ]);
        $r->assertRedirect(route('admin.dashboard'));
    }

    public function test_11_03_connexion_mauvais_mot_de_passe(): void
    {
        $r = $this->post(route('admin.login.submit'), [
            'email'        => $this->admin->email,
            'mot_de_passe' => 'mauvais_mdp_xxxx',
        ]);
        $r->assertSessionHasErrors();
    }

    public function test_11_04_connexion_compte_non_admin(): void
    {
        $nonAdmin = User::where('role', 'particulier')->firstOrFail();
        $r = $this->actingAs($nonAdmin)->get(route('admin.dashboard'));
        $r->assertStatus(403);
    }

    public function test_11_05_deconnexion_admin(): void
    {
        $r = $this->actingAs($this->admin)->post(route('admin.admin.logout'));
        $r->assertRedirect();
        $this->assertGuest();
    }
}
