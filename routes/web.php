<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Owner\ProfileController;
use App\Http\Controllers\Owner\DashboardController;
use App\Http\Controllers\Owner\AdController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\TypeServiceController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\VtcAdminController;
use App\Http\Controllers\Admin\SubCategoryController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\livrer\CarteVtcController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ContactController;
use App\Http\Middleware\AdminMiddleware;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/creer-site', function () {
    return view('pages.creer_site');
})->name('creer.site');


Route::get('/contact', [ContactController::class, 'index'])
    ->name('contact');

Route::post('/contact', [ContactController::class, 'store'])
    ->name('contact.store');


Route::get('/annonce-details', function () {
    return view('pages.annonces_pages.annonces_details');
})->name('annonces.details');

Route::view('/categories', 'pages.annonces_pages.categories_annonces')
->name('categories');

// Route::get('/deposer_annonce', function () {
//     return view('pages.locateur.deposer_annonce');
// })->name('deposer_annonce'); 

Route::get('/favoris', function () {
    return view('pages.locateur.favoris');
})->name('favoris');

Route::get('/profile', function () {
    return view('pages.locateur.profile');
})->name('profile');

Route::get('/statistiques', function () {
    return view('pages.locateur.statistiques');
})->name('statistiques');

// Route::get('/mes_annonces', function () {
//     return view('pages.locateur.mes_annonces');
// })->name('mes_annonces');

Route::get('/messages', function () {
    return view('pages.locateur.messages');
})->name('messages');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'profile'])->name('profile');
    Route::get('/profile/modifer', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile/modifer', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/supprimer', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/annonces/deposer-une-annonce', [AdController::class, 'create'])->name('ads.create');
    Route::post('/ads', [AdController::class, 'store'])->name('ads.store');
    Route::get('/ads/reverse-geocode', [AdController::class, 'reverseGeocode'])->name('ads.reverse-geocode');
    Route::get('/annonces/mes-annonces', [AdController::class, 'index'])->name('ads.index');
    Route::get('/annonces/{ad}/ical', [AdController::class, 'exportICal'])->name('ads.ical');
    Route::get('/annonces/{ad}/modifier', [AdController::class, 'edit'])->name('ads.edit');
    Route::put('/annonces/{ad}', [AdController::class, 'update'])->name('ads.update');
    Route::delete('/annonces/{ad}', [AdController::class, 'destroy'])->name('ads.destroy');
});
    // Login admin public
    Route::get('admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
    Route::post('admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
// Routes admin protégées
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin']) ->group(function () {

    // Catégories
    Route::get('/categorie', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    // Dashboard
    Route::get('/', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('dashboard');
    // Sous-catégories
    Route::resource('subcategories', SubCategoryController::class);

    // Services
    Route::get('services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('services/create', [ServiceController::class, 'create'])->name('services.create');
    Route::post('services', [ServiceController::class, 'store'])->name('services.store');
    Route::get('services/{service}/edit', [ServiceController::class, 'edit'])->name('services.edit');
    Route::put('services/{service}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');

    // Type de services
    Route::get('type_services', [TypeServiceController::class, 'index'])->name('type_services.index');
    Route::get('type_services/create', [TypeServiceController::class, 'create'])->name('type_services.create');
    Route::post('type_services', [TypeServiceController::class, 'store'])->name('type_services.store');
    Route::get('type_services/{typeService}/edit', [TypeServiceController::class, 'edit'])->name('type_services.edit');
    Route::put('type_services/{typeService}', [TypeServiceController::class, 'update'])->name('type_services.update');
    Route::delete('type_services/{typeService}', [TypeServiceController::class, 'destroy'])->name('type_services.destroy');

    // Messages de contact
    Route::get('contact-messages', [ContactMessageController::class, 'index'])->name('contact_messages.index');
    Route::get('contact-messages/{contactMessage}', [ContactMessageController::class, 'show'])->name('contact_messages.show');
    Route::delete('contact-messages/{contactMessage}', [ContactMessageController::class, 'destroy'])->name('contact_messages.destroy');
    // Gestion des utilisateurs
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/admin/users/{user}/verify', [UserController::class, 'verify'])->name('users.verify');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::patch('users/{user}', [UserController::class, 'update'])->name('users.update');
    // carte VTC
    Route::get('vtc-cards', [VtcAdminController::class, 'index'])->name('vtc_cards.index');
    Route::post('vtc-cards/{document}/approve', [VtcAdminController::class, 'approve'])->name('vtc_cards.approve');
    Route::post('vtc-cards/{document}/reject', [VtcAdminController::class, 'reject'])->name('vtc_cards.reject');
    // Logout admin
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
});


Route::prefix('livreur')->group(function () {
    Route::post('/documents/upload', [CarteVtcController::class, 'store'])->name('documents.upload');
    Route::get('/livreur/carte-vtc', [CarteVtcController::class, 'index'])->name('livreur.carte.vtc');
});

require __DIR__.'/auth.php';

