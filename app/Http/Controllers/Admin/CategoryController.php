<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Service;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
        $this->normalizeSlug($request);

        $request->validate($this->rules($request));

        $data = $request->only('nom', 'description', 'service_id', 'icon', 'slug');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        Category::create($data);

        return redirect()->route('admin.categories.index')->with('success', 'Catégorie ajoutée avec succès.');
    }


    // Affiche le formulaire d'édition
    public function edit(Category $category)
        { $services = Service::all();

        return view('admin.categories.edit', compact('category','services'));
    }

    public function update(Request $request, Category $category)
    {
        $this->normalizeSlug($request);

        $request->validate($this->rules($request, $category));

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

    /**
     * Le nom et le slug d'une categorie sont uniques a l'interieur de son
     * service, pas sur toute la table : "Beaute & Bien-etre" peut exister
     * a la fois sous Vente et sous Prestations de services.
     */
    private function rules(Request $request, ?Category $category = null): array
    {
        $sameService = fn ($query) => $query->where('service_id', $request->input('service_id'));

        return [
            'nom' => [
                'required', 'string', 'max:100',
                Rule::unique('categories', 'nom')->where($sameService)->ignore($category),
            ],
            'slug' => [
                'required', 'string', 'max:255',
                Rule::unique('categories', 'slug')->where($sameService)->ignore($category),
            ],
            'service_id'  => ['required', 'exists:services,id'],
            'description' => ['nullable', 'string'],
            'icon'        => ['nullable', 'string', 'max:255'],
            'image'       => ['nullable', 'image', 'max:4096'],
        ];
    }

    /**
     * Le modele normalise le slug a l'enregistrement : on applique la meme
     * transformation avant de valider, sinon le controle d'unicite porterait
     * sur une valeur differente de celle reellement stockee.
     */
    private function normalizeSlug(Request $request): void
    {
        $request->merge([
            'slug' => Str::slug($request->input('slug') ?: $request->input('nom')),
        ]);
    }
}
