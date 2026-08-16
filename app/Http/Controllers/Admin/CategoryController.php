<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Service;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::with('service');

        if ($request->filled('search')) {
            $query->where('nom', 'LIKE', "%{$request->search}%");
        }

        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        $categories = $query->latest()->paginate(10)->withQueryString();
        $services = Service::all();

        return view('admin.categories.index', compact('categories', 'services'));
    }

    // Affiche le formulaire de création
    public function create()
    { $services = Service::all();
        return view('admin.categories.create', compact( 'services'));
    }

    // Enregistre une nouvelle catégorie
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable',
            'slug' => 'required|string|unique:categories,slug',
        ]);

        $data = $request->only('nom', 'description', 'service_id', 'icon', 'slug');

        if ($request->hasFile('image')) {
            if(isset($category) && $category->image){
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        Category::create($data); // pour store
        return redirect()->route('admin.categories.index')->with('success', 'Catégorie ajoutée avec succès.');
    }


    // Affiche le formulaire d'édition
    public function edit(Category $category)
        { $services = Service::all();

        return view('admin.categories.edit', compact('category','services'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable',
            'slug' => 'required|string|unique:categories,slug,' . $category->id,
        ]);

        $data = $request->only('nom', 'description', 'service_id', 'icon', 'slug');

        // Si l'utilisateur upload une nouvelle image
        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image si elle existe
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }

            // Sauvegarder la nouvelle image
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('success', 'Catégorie mise à jour avec succès.');
    }

    // Supprime une catégorie
    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('admin.categories.index')
                         ->with('success', 'Catégorie supprimée avec succès.');
    }
}
