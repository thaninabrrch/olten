<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Owner\ProfileController;
use App\Http\Controllers\Owner\DashboardController;
use App\Http\Controllers\Owner\AdController;
use App\Http\Controllers\Admin\AdadController;
use App\Http\Controllers\Owner\MessageController;
use App\Http\Controllers\Owner\FavoriteController;
use App\Http\Controllers\Owner\StatsController;
use App\Http\Controllers\Owner\AdReportController;
use App\Http\Controllers\Seller\ProductController;
use App\Http\Controllers\Seller\SellerController;
use App\Http\Controllers\Seller\SellerOrderController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\CovoiturageAdminController;
use App\Http\Controllers\Admin\TypeServiceController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\VtcAdminController;
use App\Http\Controllers\Admin\SubCategoryController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\livrer\CarteVtcController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\CovoiturageController;
use App\Http\Controllers\VehicleController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\livrer\DeliveryAdController;
use App\Http\Controllers\livrer\AdsLivreurController;
use App\Models\LivraisonColis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\SubscriptionController;

Route::get('rapport-test', [AdminDashboardController::class, 'rapportTest'])->name('rapport_test');
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth'])
    ->group(function () {

        Route::get('/settings', [SettingController::class, 'index'])
            ->name('settings.index');

    });
Route::get('/', [HomeController::class, 'home'])->name('home');
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

Route::get('/compte-en-attente', function () { return view('auth.pending-approval');})->name('account.pending');
Route::get('/verify-email', function () { return view('auth.verify-email');})->name('account.verify');

Route::middleware('auth')->group(function () {
    Route::get('/abonnements', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('/abonnements/{slug}/choisir', [SubscriptionController::class, 'select'])->name('subscriptions.select');
    Route::get('/abonnements/{subscription}/paiement', [SubscriptionController::class, 'payment'])->name('subscriptions.payment');
    Route::get('/abonnements/paiement/success', [SubscriptionController::class, 'success'])->name('subscriptions.success');
    Route::get('/abonnements/paiement/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
});

Route::middleware('auth', 'verified', 'approved', 'subscription', 'role:locateur', 'subscription.level:standard,premium,vip')->group(function () {
    Route::get('/annonces/deposer-une-annonce', [AdController::class, 'create'])->name('ads.create');
    Route::post('/ads', [AdController::class, 'store'])->name('ads.store');
    Route::get('/ads/reverse-geocode', [AdController::class, 'reverseGeocode'])->name('ads.reverse-geocode');
    Route::get('/annonces/mes-annonces', [AdController::class, 'index'])->name('ads.index');
    Route::get('/mes-reservations-recues', [BookingController::class, 'receivedBookings'])->name('bookings.receivedBookings');
    Route::patch('/bookings/{booking}/accept', [BookingController::class, 'accept'])->name('bookings.accept');
    Route::patch('/bookings/{booking}/reject', [BookingController::class, 'reject'])->name('bookings.reject');
    Route::get('/annonces/{ad}/ical', [AdController::class, 'exportICal'])->name('ads.ical');
    Route::get('/annonces/{ad}/modifier', [AdController::class, 'edit'])->name('ads.edit');
    Route::put('/annonces/{ad}/modifier', [AdController::class, 'update'])->name('ads.update');
    Route::delete('/annonces/{ad}/supprimer', [AdController::class, 'destroy'])->name('ads.destroy');
    Route::delete('/ads/images/{image}', [AdController::class, 'destroyImgs'])->name('ads.images.destroy');
    Route::get('/stats/ads', [StatsController::class, 'adsStats'])->name('stats.ads');
    Route::get('/statistiques', function () {return view('pages.locateur.statistiques');})->name('statistiques');
});

Route::middleware('auth', 'verified', 'approved', 'subscription', 'role:livreur', 'subscription.level:standard,premium,vip')->group(function () {
    Route::get('/espace-livraison/missions', [DeliveryAdController::class, 'missions'])->name('livreur.missions');
    Route::get('/espace-livraison/demandes', [DeliveryAdController::class, 'demandes'])->name('livreur.demandes');
    Route::get('/espace-livraison/livraisons-en-cours', [DeliveryAdController::class, 'livraisons'])->name('livreur.livraisons');
    Route::post('/espace-livraison/livraison/{delivery}/finaliser', [DeliveryAdController::class, 'finaliserMission'])->name('livreur.livraison.finaliser');
    Route::post('/espace-livraison/{delivery}/pickup', [DeliveryAdController::class, 'pickupDelivery'])->name('livreur.livraison.pickup');
    Route::post('/espace-livraison/{delivery}/start', [DeliveryAdController::class, 'startDelivery'])->name('livreur.livraison.start');
    Route::get('/livraison.termine', [DeliveryAdController::class, 'historiqueTermine'])->name('liv_termine');
    Route::post('/delivery/ads/{ad}/{type}/request', [DeliveryAdController::class, 'sendRequest'])->name('delivery.ads.request');
});

Route::middleware('auth', 'verified', 'subscription', 'approved')->group(function () {
    Route::post('/profile/toggle-vtc', [ProfileController::class, 'toggleVtc'])->name('profile.toggleVtc');
    Route::post('/profile/toggleLivreur', [ProfileController::class, 'toggleLivreur'])->name('profile.toggleLivreur');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'profile'])->name('profile');
    Route::get('/profile/modifer', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile/modifer', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/supprimer', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth', 'verified', 'subscription', 'approved', 'subscription.level:standard,premium,vip')->group(function () {
    Route::get('/mes-reservations', [BookingController::class, 'myBookings'])->name('bookings.myBookings');
    Route::get('/mes-messages', function () {
        return view('pages.locateur.messages');
    })->name('messages');
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{user}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{user}', [MessageController::class, 'store'])->name('messages.store');
    Route::post('/ads/{ad}/favorite', [FavoriteController::class, 'toggle'])->name('ads.favorite');
    Route::get('/favoris', [FavoriteController::class, 'index'])->name('favoris');
    Route::post('/ads/{ad}/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/confirm', [BookingController::class, 'confirm'])->name('bookings.confirm');
    Route::post('/bookings/pay', [BookingController::class, 'pay'])->name('bookings.pay');
    Route::post('/ads/{ad}/report', [AdReportController::class, 'store'])->middleware('auth')->name('ads.report');
    Route::get('/portefeuille', [WalletController::class, 'index'])->name('walt.index');
    Route::prefix('livreur')->group(function () {
        Route::post('/documents/upload', [CarteVtcController::class, 'store'])->name('documents.upload');
        Route::get('/livreur/carte-vtc', [CarteVtcController::class, 'index'])->name('livreur.carte.vtc');
    });
    Route::get('/demandes-de-livraison', [AdsLivreurController::class, 'index'])->name('livreur.ads.index');
    Route::post('/demandes/{demande}/accept', [AdsLivreurController::class, 'acceptDemande'])->name('delivery.request.accept');
    Route::post('/demandes/{demande}/refuse', [AdsLivreurController::class, 'refuseDemande'])->name('delivery.request.refuse');
    Route::post('/demandes/{demande}/annuler', [AdsLivreurController::class, 'annulerMission'])->name('demande.annuler');
    Route::get('/covoiturage', [CovoiturageController::class, 'index'])
        ->name('covoiturage.index');
    Route::get('/covoiturage/create', [CovoiturageController::class, 'create'])
        ->name('covoiturage.create');
    Route::post('/covoiturage/publish', [CovoiturageController::class, 'publish'])->middleware('auth');
    Route::get('/trajet/{covoiturage}', [CovoiturageController::class, 'show'])
        ->name('trajet.show');
    Route::delete('/covoiturage/{id}', [CovoiturageController::class, 'destroy'])
        ->name('covoiturage.destroy');
    Route::get('/covoiturage/{id}/edit', [CovoiturageController::class, 'edit'])
    ->name('covoiturage.edit');
    Route::post('/covoiturage/{covoiturage}/dupliquer', [CovoiturageController::class, 'dupliquer'])->name('covoiturage.dupliquer');
    Route::put('/covoiturage/{id}', [CovoiturageController::class, 'update'])
        ->name('covoiturage.update');
    Route::get('/covoiturage/{id}/options', [CovoiturageController::class, 'editOptions'])->name('covoiturage.options.edit');
    Route::post('/covoiturage/{id}/options', [CovoiturageController::class, 'updateOptions'])->name('covoiturage.options.update');
    Route::get('/covoiturage/{id}/prix', [CovoiturageController::class, 'editPrice'])
    ->name('covoiturage.prix.edit');
    Route::post('/covoiturage/{id}/prix', [CovoiturageController::class, 'updatePrice'])
        ->name('covoiturage.prix.update');
    Route::get('/covoiturage/{id}/edititen', [CovoiturageController::class, 'edititen'])
    ->name('covoiturage.edititen.edit');
    Route::get('/covoiturage/{id}/edit-date-time', [CovoiturageController::class, 'editDateTime'])
        ->name('covoiturage.edit-date-time');
    Route::post('/covoiturage/{id}/update-date-time', [CovoiturageController::class, 'updateDateTime'])
        ->name('covoiturage.update-date-time');

    Route::get('/covoiturage/{covoiturage}/edit-route', [CovoiturageController::class, 'editRoute'])
        ->name('covoiturage.edit-route');

    Route::post('/covoiturage/{covoiturage}/update-route', [CovoiturageController::class, 'updateRoute'])
        ->name('covoiturage.update-route');
    Route::get('/covoiturage/{id}/edit-retour', [CovoiturageController::class, 'editRetour'])
        ->name('covoiturage.edit-retour');
    Route::put('/covoiturage/{id}/update-retour', [CovoiturageController::class, 'updateRetour'])
        ->name('covoiturage.update-retour');
    Route::put('/covoiturage/{id}/toggle-retour', [CovoiturageController::class, 'toggleRetour']);
    Route::get('/covoiturage/{id}/add-retour', [CovoiturageController::class, 'addRetour'])
    ->name('covoiturage.add-retour');
    Route::post('/covoiturage/{id}/store-retour', [CovoiturageController::class, 'storeRetour'])
        ->name('covoiturage.store-retour');
    Route::get('/covoiturage/{id}/edit-mode', [CovoiturageController::class, 'editMode'])
    ->name('covoiturage.editMode');

    Route::post('/covoiturage/{id}/update-mode', [CovoiturageController::class, 'updateMode'])
        ->name('covoiturage.updateMode');
    Route::get('/vehicle/edit', [VehicleController::class, 'edit'])->name('vehicle.edit');
    Route::post('/vehicle/update', [VehicleController::class, 'update'])->name('vehicle.update');

    Route::get('mes-commandes', [SellerOrderController::class, 'orders'])->name('orders');
    Route::get('/mes-commandes/{order}', [SellerOrderController::class, 'show'])->name('orders.show');
    Route::post('/{delivery}/rate', [DeliveryAdController::class, 'rateDelivery'])->name('delivery.rate');
    Route::get('/mes-reservations/{booking}', [BookingController::class, 'show'])->name('bookings.show');
});
//visualiser le détails d'une annonce meme pour un utilisateur visteur non connecté
Route::get('/annonces/{ad}/détails', [AdController::class, 'show'])->name('ads.show');

// Login admin public
Route::get('admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
// Routes admin protégées
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin']) ->group(function () {
    // covoiturage
    Route::get('rides', [CovoiturageAdminController::class, 'index'])->name('rides.index');
    Route::patch('rides/{ride}/toggle-status', [CovoiturageAdminController::class, 'toggleStatus'])->name('rides.toggle-status');
    // Catégories
    Route::get('/categorie', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    // Dashboard
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
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
    // annonce
    Route::get('/ads/admin', [AdadController::class, 'index'])->name('admin.ads.index');
    Route::patch('ads/{ad}/approve', [AdadController::class, 'approve'])->name('ads.approve');
    Route::patch('ads/{ad}/reject', [AdadController::class, 'reject'])->name('ads.reject');
    // Rapport de test PDF

    // Logout admin
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
});

// vendeur
Route::prefix('vendeur')
    ->name('seller.')
    ->middleware(['auth', 'role:vendeur', 'approved'])
    ->group(function () {
        Route::resource('produits', ProductController::class);
        Route::delete('product-images/{id}', [ProductController::class, 'deleteImage'])->name('seller.product.image.delete');
        Route::get('ventes', [SellerController::class, 'sales'])->name('sales');
        Route::get('ventes/{sale}', [SellerController::class, 'showSale'])->name('sales.show');
        Route::post('ventes/{sale}/delivered', [SellerController::class, 'markAsDelivered'])->name('sales.delivered');
        Route::get('ventes/{sale}/invoice', [SellerController::class, 'invoice'])->name('sales.invoice');
        Route::post('ventes/{sale}/paid', [SellerController::class, 'markAsPaid'])->name('sales.paid');
        Route::get('orders/{order}', [SellerOrderController::class, 'showOrder'])->name('orders.show');
        Route::get('commandes-clients', [SellerOrderController::class, 'clientOrders'])->name('clientOrders');
        Route::post('commandes-clients/{order}/cancel', [SellerOrderController::class, 'cancelOrder'])->name('orders.cancel');
        Route::post('commandes-clients/{order}/confirmer', [SellerOrderController::class, 'confirmOrder'])->name('orders.confirm');
    });
Route::prefix('produits')
    ->name('products.')
    ->middleware(['auth', 'role:vendeur', 'approved'])
    ->group(function () {
        Route::get('confirm', [ProductController::class, 'confirm'])->name('confirm')->middleware('auth');
        Route::post('pay', [ProductController::class, 'pay'])->name('pay')->middleware('auth');
        Route::get('success', function () {
            return view('products.success');
        })->name('success');
        Route::post('{product}/acheter', [ProductController::class, 'purchase'])->name('purchase')->middleware('auth');
        Route::get('{product}', [ProductController::class, 'show'])->name('show');
    });
Route::get('/{slug}', [HomeController::class, 'show'])->name('categories.show');
require __DIR__.'/auth.php';
