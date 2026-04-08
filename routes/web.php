<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
});


Route::get('/creer-site', function () {
    return view('pages.creer_site');
})->name('creer.site');

Route::get('/contact', function () {
    return view('pages.contact');
})->name('contact');

Route::get('/annonce-details', function () {
    return view('pages.annonces_pages.annonces_details');
})->name('annonces.details');

Route::view('/categories', 'pages.annonces_pages.categories_annonces')
->name('categories');

Route::get('/dashboard-locateur', function () {
    return view('pages.locateur.dashboard');
});

Route::get('/deposer_annonce', function () {
    return view('pages.locateur.deposer_annonce');
})->name('deposer_annonce'); 

Route::get('/favoris', function () {
    return view('pages.locateur.favoris');
})->name('favoris');

Route::get('/profile', function () {
    return view('pages.locateur.profile');
})->name('profile');

Route::get('/statistiques', function () {
    return view('pages.locateur.statistiques');
})->name('statistiques');

Route::get('/mes_annonces', function () {
    return view('pages.locateur.mes_annonces');
})->name('mes_annonces');

Route::get('/messages', function () {
    return view('pages.locateur.messages');
})->name('messages');

Route::get('/intermediaire-transport', function () {
    return view('pages.intermediaire_transport');
})->name('intermediaire.transport');

Route::get('/location-vehicule', function () {
    return view('pages.location_vehicule');
})->name('location.vehicule');

Route::get('/detail-trajet', function () {
    return view('pages.locateur.detail-trajet');
})->name('detail.trajet');

Route::get('/covoiturages', function () {
    return view('pages.covoiturages');
})->name('covoiturages');